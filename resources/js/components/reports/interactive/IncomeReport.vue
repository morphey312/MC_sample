<template>
    <page
        :title="__('Отчеты Инкам V2')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-grey insurance-service-act">
            <el-tab-pane
                :lazy="true"
                :label="__('Таблица')"
                name="table" >
                <report-table 
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Раздельный')"
                name="charts" >
                <separated-charts 
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :clinic-list="clinicList"
                    :payment-index="paymentIndex"
                    :specialization-list="specializationList"
                    :clinic-label-map="clinicLabelMap" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Приход+Пациенты')"
                name="mix-chart" >
                <mix-chart
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :clinic-list="clinicList"
                    :payment-index="paymentIndex"
                    :specialization-list="specializationList"
                    :clinic-label-map="clinicLabelMap" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Сравнение периодов')"
                name="period-compare-chart" >
                <compare-chart 
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex"
                    :clinic-list="clinicList"
                    :specialization-list="specializationList"
                    :clinic-label-map="clinicLabelMap" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('По врачам')"
                name="doctor-chart" >
                <doctor-mix-chart
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex"
                    :clinic-list="clinicList"
                    :specialization-list="specializationList" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Периоды')"
                name="period-chart" >
                <period-chart
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex" 
                    :clinic-list="clinicList"
                    :specialization-list="specializationList" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Прогноз')"
                name="prognosis-chart" >
                <prognosis-chart
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex" 
                    :clinic-list="clinicList"
                    :clinic-label-map="clinicLabelMap" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Доход по дням')"
                name="daily-table" >
                <daily-report
                    :elastic-search-client="elasticSearchClient"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex" 
                    :clinic-list="clinicList" />
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>
import ElasticSearchClient from '@/services/elasticsearch';
import AppointmentStatusRepository from '@/repositories/appointment/status';
import ReportTable from './income/ReportTable.vue';
import SeparatedCharts from './income/Charts.vue';
import MixChart from './income/MixChart.vue';
import CompareChart from './income/CompareChart.vue';
import DoctorMixChart from './income/DoctorMixChart.vue';
import PeriodChart from './income/PeriodChart.vue';
import PrognosisChart from './income/PrognosisChart.vue';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import CONSTANTS from '@/constants';
import DailyReport from './income/DailyReport.vue';

export default {
    components: {
        ReportTable,
        SeparatedCharts,
        MixChart,
        CompareChart,
        DoctorMixChart,
        PeriodChart,
        PrognosisChart,
        DailyReport,
    },
    data() {
        return {
            elasticSearchClient: new ElasticSearchClient(),
            activeTab: 'table',
            activeStatuses: [],
            clinicList: [],
            specializationList: [],
            tableData: [],
            clinicLabelMap: {},
        }
    },
    computed: {
        appointmentIndex() {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.INCOME_APPOINTMENTS);
        },
        paymentIndex() {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.INCOME_PAYMENTS);
        },
    },
    mounted() {
        this.getStatuses();
        this.getClinics();
        this.getSpecializations();
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
                this.clinicList = response;
                this.clinicList.forEach(clinic => {
                    this.clinicLabelMap[clinic.value] = _.isFilled(clinic.short_name) ? clinic.short_name : clinic.value;
                });
                return Promise.resolve();
            });
        },
        getSpecializations() {
            let specialization = new SpecializationRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            return specialization.fetchList().then(response => {
                this.specializationList = response;
                return Promise.resolve();
            });
        },
    }
}
</script>