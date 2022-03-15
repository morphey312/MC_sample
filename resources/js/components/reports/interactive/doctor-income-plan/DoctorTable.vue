<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="clinics"
                        property="clinic"
                        :filterable="true"
                        :label="__('Клиника')" />
                </el-col>
                <el-col :span="6">
                    <form-date
                        :entity="filters"
                        property="date_start"
                        :label="__('Дата от')" />
                </el-col>
                <el-col :span="6">
                    <form-date
                        :entity="filters"
                        property="date_end"
                        :label="__('Дата до')" />
                </el-col>
            </el-row>
            <div class="buttons">
                <el-button
                    @click="getReportData()"
                    type="primary">
                    {{ __('Показать') }}
                </el-button>
            </div>
        </section>
        <section class="p-0" v-if="!loading && tableData.length != 0">
            <data-table
                :table-data="tableData"
                :months="months"
            />
        </section>
    </div>
</template>
<script>
import DoctorIncomePlanRepository from '@/repositories/employee/doctor-income-plan';
import CONSTANTS from '@/constants';
import doctorTableGenerator from './doctor-table-generator';
import DataTable from './DoctorPlanTable.vue';
import ElasticReportMixin from '@/components/reports/interactive/doctor-income-plan/mixins/elastic';

export default {
    mixins: [
        ElasticReportMixin,
    ],
    components: {
        DataTable,
    },
    data() {
        return {
            filters: {
                clinic: null,
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
            },
            sessions: [],
            doctorPlans: [],
            tableData: [],
            months: {},
        }
    },
    methods: {
        filterIsEmpty() {
            return _.isVoid(this.filters.clinic);
        },
        getReportData() {
            if (this.filterIsEmpty()) {
                return this.$error(__('Выберите фильтры для поиска'));
            }
            this.loading = true;
            this.getDaySheetData().then(() => {
                this.fetchData().then(() => {
                    this.loading = false;
                });
            });
        },
        fetchData() {
            return this.getAppointments(this.filters).then(appointments => {
                return this.getPayments(this.filters).then(payments => {
                    let result = doctorTableGenerator(payments, appointments, this.filters, this.sessions, this.doctorPlans);
                    this.tableData = result.tableData;
                    this.months = result.months;
                    return Promise.resolve();
                });
            });
        },
        getDaySheetData() {
            let daySheetFilters = this.getDaySheetFilters(this.filters);
            return Promise.all([this.getDaySheets(daySheetFilters), this.getIncomePlans()])
                .then(response => {
                    this.sessions = response[0];
                    this.doctorPlans = response[1];
                });
        },
        getIncomePlans() {
            let plan = new DoctorIncomePlanRepository();
            let filters = {
                clinic: this.filters.clinic,
                year: new Date(this.filters.date_start).getFullYear(),
            };
            return plan.fetchList(filters, null, ['specialization']);
        },
        getPayments(filters) {
            return this.elasticSearchClient.getHits(this.paymentIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                sort: [{ id : "asc" }],
                query: {
                    bool: {
                        filter: [
                            {
                                range: {
                                    created_at: {
                                        gte: filters.date_start,
                                        lte: filters.date_end
                                    }
                                },
                            },
                            {
                                term: {
                                    clinic_id: filters.clinic
                                }
                            },
                            {
                                term: {
                                    is_deleted: false,
                                }
                            },
                        ]
                    }
                }
            }, true)
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
        getAppointments(filters) {
            return this.elasticSearchClient.getHits(this.appointmentIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                sort: [{ id : "asc" }],
                query: {
                    bool: {
                        filter: [
                            {
                                range: {
                                    date: {
                                        gte: filters.date_start,
                                        lte: filters.date_end
                                    }
                                },
                            },
                            {
                                term: {
                                    clinic_id: filters.clinic
                                }
                            },
                            {
                                term: {
                                    is_deleted: false,
                                }
                            },
                            {
                                terms: {
                                    appointment_status_id: this.activeStatuses
                                }
                            },
                        ]
                    }
                }
            }, true)
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
    },
}
</script>
