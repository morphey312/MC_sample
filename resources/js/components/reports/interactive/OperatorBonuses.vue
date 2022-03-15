<template>
    <page
        :title="__('Бонусы операторов')"
        type="flex"
        v-loading="loading">
        <el-tabs v-model="activeTab" class="tab-group-grey insurance-service-act">
            <el-tab-pane
                :lazy="true"
                :label="__('Сводная')"
                name="summary-table" >
                <summary-report
                    :elastic-search-client="elasticSearchClient"
                    :bonus-index="bonusIndex"
                    :wrapup-index="wrapupIndex"
                    :clinics="clinics"
                    :positions="positions"
                    :operators="operators"
                    :clinic-bonuses="clinicBonuses"  />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('По клиникам')"
                name="clinic-table" >
                <clinics-report
                    :elastic-search-client="elasticSearchClient"
                    :bonus-index="bonusIndex"
                    :wrapup-index="wrapupIndex"
                    :clinics="clinics"
                    :positions="positions"
                    :operators="operators"
                    :clinic-bonuses="clinicBonuses" />
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>
import SummaryReport from './operator-bonus/SummaryReport.vue';
import ClinicsReport from './operator-bonus/ClinicsReport.vue';
import ElasticSearchClient from '@/services/elasticsearch';
import ClinicRepository from '@/repositories/clinic';
import PositionRepository from '@/repositories/employee/position';
import EmployeeRepository from '@/repositories/employee';
import OperatorBonusesReportRepository from '@/repositories/reports/call-center/operator-bonuses';
import CONSTANTS from '@/constants';

export default {
    components: {
        SummaryReport,
        ClinicsReport,
    },
    data() {
        return {
            elasticSearchClient: new ElasticSearchClient(),
            activeTab: 'summary-table',
            clinics: [],
            positions: [],
            operators: [],
            clinicBonuses: [],
            loading: false,
        }
    },
    computed: {
        bonusIndex()  {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.CALL_CENTER_BONUSES);
        },
        wrapupIndex()  {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.CALL_CENTER_SESSION_LOGS);
        },
    },
    mounted() {
        this.loading = true;
        this.getClinicBonuses();
        this.getClinics().then(() => {
            this.getPositions();
        });
    },
    methods: {
        getClinicBonuses() {
            let repo = new OperatorBonusesReportRepository();
            repo.fetchClinicNorms().then(response => {
                this.clinicBonuses = [];
                if (response && response.data) {
                    this.clinicBonuses = response.data;
                }
            });
        },
        getClinics() {
            let clinic = new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            return clinic.fetchList().then(clinics => {
                this.clinics = clinics;
                return Promise.resolve();
            });
        },
        getPositions() {
            let position = new PositionRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            let filters = {
                or: [
                    {is_operator: true},
                    {is_superviser: true},
                ]
            };
            return position.fetchList(filters).then(positions => {
                this.positions = positions;
                return this.getOperators();
            });
        },
        getOperators() {
            let employee = new EmployeeRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            let filters = {
                employee_clinic: _.onlyFilled({
                    position: this.positions.map(p => p.id),
                    status_not: [
                        CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                        CONSTANTS.EMPLOYEE.STATUSES.REMOVED,
                    ]
                }),
            };
            return employee.fetchReportList(filters, null, ['only_clinic', 'operator_bonus']).then(operators => {
                this.operators = operators;
                this.loading = false;
                return Promise.resolve();
            });
        }
    },
}
</script>
