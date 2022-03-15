<template>
    <page
        v-loading="loading"
        :title="__('Отчет Планирование')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-grey insurance-service-act">
            <el-tab-pane
                :lazy="true"
                :label="__('По врачам')"
                name="doctor-table" >
                <doctor-table
                    :elastic-search-client="elasticSearchClient"
                    :clinics="clinics"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('План факт')"
                name="clinic-periods" >
                <plan-fact-report
                    :elastic-search-client="elasticSearchClient"
                    :clinics="clinics"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Рейтинг')"
                name="ratings" >
                <ratings-report
                    :elastic-search-client="elasticSearchClient"
                    :clinics="clinics"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Total')"
                name="report-total" >
                <total-report
                    :elastic-search-client="elasticSearchClient"
                    :clinics="clinics"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Сравнение')"
                name="report-compare" >
                <compare-report
                    :elastic-search-client="elasticSearchClient"
                    :clinics="clinics"
                    :active-statuses="activeStatuses"
                    :appointment-index="appointmentIndex"
                    :payment-index="paymentIndex"
                    :clinic-label-map="clinicLabelMap" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Возвраты')"
                name="report-refunds" >
                <refunds-report
                    :elastic-search-client="elasticSearchClient"
                    :clinics="clinics"
                    :payment-index="paymentIndex" />
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>
import DoctorTable from './doctor-income-plan/DoctorTable.vue';
import PlanFactReport from './doctor-income-plan/PlanFactReport.vue';
import RatingsReport from './doctor-income-plan/RatingsReport.vue';
import TotalReport from './doctor-income-plan/TotalReport.vue';
import CompareReport from './doctor-income-plan/CompareReport.vue';
import RefundsReport from './doctor-income-plan/RefundsReport.vue';
import ElasticSearchClient from '@/services/elasticsearch';
import ClinicRepository from '@/repositories/clinic';
import AppointmentStatusRepository from '@/repositories/appointment/status';
import CONSTANTS from '@/constants';

export default {
    components: {
        DoctorTable,
        PlanFactReport,
        RatingsReport,
        TotalReport,
        CompareReport,
        RefundsReport,
    },
    data() {
        return {
            activeTab: 'doctor-table',
            elasticSearchClient: new ElasticSearchClient(),
            clinics: [],
            activeStatuses: [],
            clinicLabelMap: {},
            loading: true,
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
        Promise.all([
            this.getStatuses(),
            this.getClinics(),
        ]).finally(() => {
            this.loading = false;
        });
    },
    methods: {
        getClinics() {
            let clinic = new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            return clinic.fetchList().then(response => {
                this.clinics = response;
                this.clinics.forEach(clinic => {
                    this.clinicLabelMap[clinic.value] = _.isFilled(clinic.short_name) ? clinic.short_name : clinic.value;
                });
                return Promise.resolve();
            });
        },
        getStatuses() {
            let status = new AppointmentStatusRepository();
            return status.fetchList({
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
    },
}

</script>
