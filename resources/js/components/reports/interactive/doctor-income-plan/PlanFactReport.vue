<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
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
                        :clearable="true"
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
import DaySheetRepository from '@/repositories/day-sheet';
import DoctorIncomePlanRepository from '@/repositories/employee/doctor-income-plan';
import DataTable from './PlanFactTable.vue';
import CONSTANTS from '@/constants';
import ElasticScriptMixin from '@/components/reports/interactive/mixins/elastic-script';
import ElasticReportMixin from '@/components/reports/interactive/doctor-income-plan/mixins/elastic';
import * as TimeMapper from '@/services/report/time-mapper';

export default {
    mixins: [
        ElasticScriptMixin,
        ElasticReportMixin,
    ],
    components: {
        DataTable,
    },
    data() {
        return {
            filters: {
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
            },
            sessionSelection: [],
            sessionFull: [],
            doctorPlans: [],
            tableData: [],
            months: {},
        }
    },
    methods: {
        getReportData() {
            this.loading = true;
            this.months = TimeMapper.getMonthData(this.filters);
            Promise.all([
                this.getIncomePlans(),
                this.countDaySheets(this.filters),
                this.countDaySheets(this.getFullSessionFilter()),
                this.getAggregations(this.filters)
            ]).then(results => {
                this.doctorPlans = results[0];
                this.sessionSelection = this.mapSession(results[1]);
                this.sessionFull = this.mapSession(results[2]);
                this.prepareClinicPlans(results[3]);
                this.loading = false;
            }).catch(e => {
                console.error(e);
                this.loading = false;
            });
        },
        getAggregations(filters) {
            let resultData = {};

            return new Promise((resolve) => {
                let getDataRows = async () => {
                    let monthKeys = Object.keys(this.months);
                    let monthCount = monthKeys.length;
                    for(let i = 0; i < monthCount; i++) {
                        let month = monthKeys[i];
                        let result = await this.getPayments({...filters, ...this.filterPeriod(this.months[month], filters)});
                        resultData[month] = result ? result.aggr_group : [];
                    }
                    resolve(resultData);
                }
                getDataRows();
            });
        },
        getFullSessionFilter() {
            let start = TimeMapper.getStartOf(this.$moment(this.filters.date_start));
            let end = TimeMapper.getEndOf(this.$moment(this.filters.date_end));
            return {
                date_start: this.$formatter.dateFormat(start, 'YYYY-MM-DD'),
                date_end: this.$formatter.dateFormat(end, 'YYYY-MM-DD'),
            };
        },
        mapSession(sessions) {
            return sessions.map(row => {
                row.month_name = row.month_name.toLowerCase();
                return row;
            });
        },
        filterPeriod(month, filters) {
            let monthStart = month.month_start >= filters.date_start ? month.month_start : filters.date_start;
            let monthEnd = month.month_end <= filters.date_end ? month.month_end : filters.date_end;

            return {
                date_start: monthStart,
                date_end: monthEnd,
            };
        },
        getIncomePlans() {
            let plan = new DoctorIncomePlanRepository();
            let filters = {
                year: new Date(this.filters.date_start).getFullYear(),
            };
            return plan.fetchList(filters);
        },
        prepareClinicPlans(paymentGroups) {
            let clinicPlans = _.groupBy(this.doctorPlans, 'clinic_id');
            let groupKeys = Object.keys(paymentGroups);
            this.tableData = [];
            let tempTableData = [];

            let monthSessionRow = {name: __('к-во раб дней')};
            Object.keys(this.months).forEach(month => {
                monthSessionRow[`plan-${month}`] = this.getMonthMaxSessions(month, this.sessionFull);
                monthSessionRow[`fact-${month}`] = this.getMonthMaxSessions(month, this.sessionSelection);
            });


            this.clinics.forEach(clinic => {
                if (!clinicPlans[clinic.id]) {
                    return;
                }
                let row = {
                    name: clinic.value,
                    currency: this.$handbook.getOption('currency', clinic.currency)
                };

                groupKeys.forEach(month => {
                    let planned = this.sumClinicPlanned(clinicPlans[clinic.id], month);
                    let fact = this.getClinicPayed(paymentGroups[month], clinic.id, clinic.currency);
                    let sessionPlan = monthSessionRow[`plan-${month}`];
                    let sessionFact = monthSessionRow[`fact-${month}`];
                    let progression = this.getProgression(planned, fact, sessionPlan, sessionFact);
                    let progressionPercent = this.getProgressionPercent(progression, planned);

                    row[`plan-${month}`] = this.$formatter.numberFormat(planned);
                    row[`fact-${month}`] = this.$formatter.numberFormat(fact);
                    row[`progression-${month}`] = this.$formatter.numberFormat(progression);
                    row[`progression-percent-${month}`] = this.$formatter.numberFormat(progressionPercent);
                    row[`sort-${month}`] = progressionPercent;
                });

                tempTableData.push(row);
            });

            let lastMonth = groupKeys[0];
            tempTableData = _.orderBy(tempTableData, `sort-${lastMonth}`, ['desc']);
            tempTableData.unshift(monthSessionRow);
            this.tableData = tempTableData;
        },
        getProgressionPercent(progression, planned) {
            if (planned === 0) return 0;
            return progression / planned * 100;
        },
        getProgression(planned, fact, sessionPlan, sessionFact) {
            return (fact / sessionFact * sessionPlan) - planned;
        },
        getMonthMaxSessions(monthName, sessions) {
            let filtered = sessions.filter(session => {
                return session.month_name == monthName;
            });
            let clinic = _.maxBy(filtered, 'sheets_count');
            return clinic ? clinic.sheets_count : 0;
        },
        getClinicPayed(payments, clinicId, currency) {
            let clinic = payments.find(payment => payment.key == clinicId);
            return clinic ? (currency === 'uah' ? clinic.payed_amount.value : clinic.base_amount.value) : 0;
        },
        sumClinicPlanned(clinicData, monthName) {
            return clinicData.reduce((total, row) => {
                return total + Number(row[monthName]);
            }, 0);
        },
        getPayments(filters) {
            return this.elasticSearchClient.getAggregations(this.paymentIndex, {
                size: 0,
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
                                    is_deleted: false,
                                }
                            },
                        ]
                    }
                },
                aggs: {
                    aggr_group: {
                        terms: {
                            size: this.clinics.length,
                            field: "clinic_id"
                        },
                        aggs: {
                            payed_amount: {
                                sum: {
                                    script: {
                                        lang: "painless",
                                        source: this.getPaymentAggrScript(),
                                    }
                                }
                            },
                            base_amount: {
                                sum: {
                                    script: {
                                        lang: "painless",
                                        source: this.getPaymentAggrScript('base_amount'), // base amount - amount in euro (data from elastic)
                                    }
                                }
                            }
                        }
                    }
                }
            })
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
