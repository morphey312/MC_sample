<template>
    <page
        :title="__('Звонки/Записи/Приходы')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-grey insurance-service-act">
            <el-tab-pane
                :lazy="true"
                :label="__('Таблица')"
                name="table" >
                <report-table 
                    :elastic-search-client="elasticSearchClient"
                    :report-index="reportIndex"
                    :clinics="clinics"
                    :positions="positions" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Динамика')"
                name="period-chart" >
                <period-chart 
                    :elastic-search-client="elasticSearchClient"
                    :report-index="reportIndex"
                    :clinics="clinics"
                    :positions="positions" />
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>
import ReportTable from './call-income/ReportTable.vue';
import PeriodChart from './call-income/PeriodChart.vue';
import ElasticSearchClient from '@/services/elasticsearch';
import ClinicRepository from '@/repositories/clinic';
import PositionRepository from '@/repositories/employee/position';
import CONSTANTS from '@/constants';

export default {
    components: {
        ReportTable,
        PeriodChart,
    },
    data() {
        return {
            elasticSearchClient: new ElasticSearchClient(),
            activeTab: 'table',
            clinics: [],
            positions: [],
        }
    },
    computed: {
        reportIndex()  {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.CALL_CENTER_SLICES);
        },
    },
    mounted() {
        this.getClinics();
        this.getPositions();
    },
    methods: {
        getClinics() {
            let clinic = new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            return clinic.fetchList().then(response => {
                this.clinics = response;
                return Promise.resolve();
            });
        },
        getPositions() {
            let position = new PositionRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            let filters = {
                or: [
                    {has_voip: true},
                    {is_reception: true},
                ]
            };
            return position.fetchList(filters).then(response => {
                this.positions = response;
                return Promise.resolve();
            });
        },
    },
}
</script>