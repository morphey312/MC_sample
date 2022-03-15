<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="clinics"
                        :multiple="true"
                        :clearable="true"
                        :filterable="true"
                        property="clinic"
                        :label="__('Клиника')" />
                </el-col>
                <el-col :span="6">
                    <form-row
                        name="dates"
                        :label="__('Период')">
                        <div class="form-input-group">
                            <form-date
                                :entity="filters"
                                property="date_start"
                                :clearable="true" />
                            <form-date
                                :entity="filters"
                                property="date_end"
                                :clearable="true" />
                        </div>
                    </form-row>
                </el-col>
            </el-row>
            <div class="buttons">
                <el-button
                    @click="getData()"
                    type="primary">
                    {{ __('Показать') }}
                </el-button>
            </div>
        </section>
        <section class="pt-0" v-if="tableData.length != 0">
            <daily-table
               :table-data="tableData"
               :clinics="reportClinics" />
        </section>
    </div>
</template>
<script>
import ElasticMixin from './mixins/elastic';
import ChartMixin from './mixins/chart';
import DailyTable from './DailyTable.vue';
import CONSTANTS from '@/constants';

const GROUP_ONE_DAY = 'day';

export default {
    mixins: [
        ElasticMixin,
        ChartMixin,
    ],
    components: {
        DailyTable,
    },
    data() {
        return {
            filters: {
                clinic: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                currency: CONSTANTS.CURRENCY.UAH,
            },
            tableData: [],
            reportClinics: [],
        }
    },
    methods: {
        getData() {
            let filters = this.prepareFilters();
            this.fetchReportData(filters);
        },
        fetchReportData(filters) {
            this.loading = true;
            this.getPayments(filters).then(payments => {
                this.prepareData(payments);
                this.loading = false;
            }).catch(e => {
                console.error(e);
                this.$error();
                this.loading = false;
            });
        },
        prepareData(payments) {
            let list = payments.aggr_group;
            let dayData = {};
            let maxClinics = [];
            list.forEach(day => {
                let formattedDate = this.$formatter.dateFormat(day.key_as_string, 'DD.MM.YYYY');
                dayData[formattedDate] = day.clinics.buckets;
                if (dayData[formattedDate].length > maxClinics.length) {
                    maxClinics = dayData[formattedDate];
                }
            });
            this.prepareClinics(maxClinics);
            this.tableData = [];
            Object.keys(dayData).forEach(date => {
                let row = {
                    date: date
                };
                let dayTotal = 0;
                dayData[date].forEach(clinic => {
                    let amount = Math.ceil(clinic.payed_amount.value);
                    row[`value-${clinic.key}`] = amount;
                    dayTotal += amount;
                })
                row['total'] = dayTotal;
                this.tableData.push(row);
            });
        },
        prepareClinics(list) {
            let presentClinics = list.map(item => item.key);
            this.reportClinics = this.clinicList.filter(clinic => {
                return presentClinics.indexOf(clinic.id) !== -1;
            });
        },
        getPayments(filters) {
            return this.elasticSearchClient.getAggregations(this.paymentIndex, {
                query: {
                    bool: {
                        filter: this.getBasePaymentFilter(filters)
                    }
                },
                aggs: {
                    aggr_group: {
                        date_histogram: {
                            field: "created_at",
                            calendar_interval: GROUP_ONE_DAY,
                        },
                        aggs: {
                            clinics: {
                                terms: {
                                    size: this.clinicList.length,
                                    field: "clinic_id",
                                },
                                aggs: {
                                    payed_amount: {
                                        sum: {
                                            script: {
                                                lang: "painless",
                                                source: this.getPaymentAggrScript(),
                                            }
                                        }
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
