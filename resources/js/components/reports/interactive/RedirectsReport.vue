<template>
    <page
        :title="__('Перенаправления V2')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-grey insurance-service-act">
            <el-tab-pane
                :lazy="true"
                :label="__('Таблица')"
                name="table" >
                <report-table
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :external-index="externalIndex"
                    :internal-index="internalIndex"
                    :clinics="clinics" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Общая')"
                name="mix-chart" >
                <separated-chart
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :external-index="externalIndex"
                    :internal-index="internalIndex"
                    :clinics="clinics" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Сравнение периодов')"
                name="period-compare-chart" >
                <compare-chart
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :external-index="externalIndex"
                    :internal-index="internalIndex"
                    :clinics="clinics" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Периоды')"
                name="period-chart" >
                <period-chart
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :external-index="externalIndex"
                    :internal-index="internalIndex"
                    :clinics="clinics" />
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>
import ReportTable from './redirects/ReportTable.vue';
import SeparatedChart from './redirects/Charts.vue';
import CompareChart from './redirects/CompareChart.vue';
import PeriodChart from './redirects/PeriodChart.vue';
import ElasticSearchClient from '@/services/elasticsearch';
import AppointmentStatusRepository from '@/repositories/appointment/status';
import ClinicRepository from '@/repositories/clinic';
import CONSTANTS from '@/constants';

export default {
    components: {
        ReportTable,
        SeparatedChart,
        CompareChart,
        PeriodChart,
    },
    data() {
        return {
            elasticSearchClient: new ElasticSearchClient(),
            activeStatuses: [],
            activeTab: 'table',
            clinics: [],
        }
    },
    computed: {
        externalIndex()  {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.REDIRECTS_EXTERNAL);
        },
        internalIndex()  {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.REDIRECTS_INTERNAL);
        },
    },
    mounted() {
        this.getStatuses();
        this.getClinics();
    },
    methods: {
        getStatuses() {
            let status = new AppointmentStatusRepository();
            status.fetchList({
                system_status_not: [
                    CONSTANTS.APPOINTMENT.STATUSES.DID_NOT_COME,
                    CONSTANTS.APPOINTMENT.STATUSES.DELETED,
                    CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP,
                    CONSTANTS.APPOINTMENT.STATUSES.PAYED,
                    CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_RECEPTION,
                ]
            }).then(response => {
                this.activeStatuses = response.map(s => s.id);
            });
        },
        getClinics() {
            let clinic = new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            return clinic.fetchList().then(response => {
                this.clinics = response;
                return Promise.resolve();
            });
        },
    },
}
</script>
