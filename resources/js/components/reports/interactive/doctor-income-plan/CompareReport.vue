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
                        :label="__('Период сравнения 1')">
                        <div class="form-input-group">
                            <form-date
                                :entity="filters"
                                property="date_start" />
                            <form-date
                                :entity="filters"
                                property="date_end" />
                        </div>
                    </form-row>
                </el-col>
                <el-col :span="6">
                    <form-row
                        name="dates"
                        :label="__('Период сравнения 2')">
                        <div class="form-input-group">
                            <form-date
                                :entity="filters"
                                property="alt_date_start" />
                            <form-date
                                :entity="filters"
                                property="alt_date_end" />
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
        <section class="p-0" v-if="!loading && tableData.length != 0">
            <data-table
                :table-data="tableData"
                :clinics-to-display="clinicsToDisplay" >
                <div class="p-10" slot="buttons">
                    <el-button
                        :disabled="tableData.length === 0"
                        @click="exportExcel">
                        {{ __('Экспорт в Excel') }}
                    </el-button>
                    <el-button
                        :disabled="tableData.length === 0"
                        @click="exportPDF">
                        {{ __('Экспорт в PDF') }}
                    </el-button>
                </div>
            </data-table>
        </section>
    </div>
</template>
<script>
import ElasticReportMixin from '@/components/reports/interactive/doctor-income-plan/mixins/elastic';
import DataTable from './CompareReportTable.vue';
import * as fileGenerator from './generator';
import FileSaver from 'file-saver';
import printer from '@/services/print';
import ComparePrint from './print/Compare.vue';
import ElasticScriptMixin from '@/components/reports/interactive/mixins/elastic-script';

const GROUP_ONE_MONTH = 'month';

export default {
    mixins: [
        ElasticReportMixin,
        ElasticScriptMixin,
    ],
    components: {
        DataTable,
    },
    props: {
        clinicLabelMap: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            filters: {
                clinic: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                alt_date_start: this.$moment().format('YYYY-MM-DD'),
                alt_date_end: this.$moment().format('YYYY-MM-DD'),
            },
            tableData: [],
            clinicsToDisplay: [],
        }
    },
    methods: {
        getData() {
            let filters = this.prepareFilters();
            let altFilters = this.getAlternateFilters(filters);
            this.loading = true;
            Promise.all([this.getPayments(filters), this.getPayments(altFilters)]).then(payments => {
                Promise.all([this.getAppointments(filters), this.getAppointments(altFilters)]).then(appointments => {
                    this.mapTableData(payments, appointments, filters);
                    this.loading = false;
                });
            });
        },
        mapTableData(payments, appointments, filters) {
            let tempData = [];
            let isFirstRow = {name: __('Количество первичных за период')};
            let isFirstRowAlt = {name: __('Количество первичных за период')};
            let incomeRow = {name: __('Итого доход за период')};
            let incomeRowAlt = {name: __('Итого доход за период')};
            let patientDiffRow = {name: __('разница в количестве первичных'), 'period-first': ''};
            let incomeDiffRow = {name: __('разница в итого Доход'), 'period-first': ''};
            let allAggrs = [
                ...payments[0].aggr_group,
                ...payments[1].aggr_group,
                ...appointments[0].aggr_group,
                ...appointments[1].aggr_group,
            ];

            this.clinicsToDisplay = this.clinics.filter(clinic => {
                let hasAggr = allAggrs.find(group => {
                    return group.key == clinic.id;
                });
                return _.isFilled(hasAggr);
            });

            this.clinicsToDisplay.forEach(clinic => {
                let firstMonth = this.getFormattedPeriod(filters.date_start);
                let altMonth = this.getFormattedPeriod(filters.alt_date_start);

                isFirstRow['period-first'] = firstMonth;
                isFirstRowAlt['period-first'] = altMonth;
                incomeRow['period-first'] = firstMonth;
                incomeRowAlt['period-first'] = altMonth;

                let isFirst = appointments[0].aggr_group.find(item => item.key == clinic.id);
                let isFirstAlt = appointments[1].aggr_group.find(item => item.key == clinic.id);
                let income = payments[0].aggr_group.find(item => item.key == clinic.id);
                let incomeAlt = payments[1].aggr_group.find(item => item.key == clinic.id);
                let isFirstCount = isFirst ? isFirst.is_first.value : 0;
                let isFirstCountAlt = isFirstAlt ? isFirstAlt.is_first.value : 0;
                let incomeSum = income ? income.payed_amount.value : 0;
                let incomeSumAlt = incomeAlt ? incomeAlt.payed_amount.value : 0;

                isFirstRow[`clinic-${clinic.id}`] = isFirstCount
                isFirstRowAlt[`clinic-${clinic.id}`] = isFirstCountAlt;
                incomeRow[`clinic-${clinic.id}`] = this.$formatter.numberFormat(incomeSum);
                incomeRowAlt[`clinic-${clinic.id}`] = this.$formatter.numberFormat(incomeSumAlt);
                patientDiffRow[`clinic-${clinic.id}`] = this.$formatter.numberFormat(this.getDifference(isFirstCountAlt, isFirstCount));
                incomeDiffRow[`clinic-${clinic.id}`] = this.$formatter.numberFormat(this.getDifference(incomeSumAlt, incomeSum));
            });

            tempData.push(isFirstRowAlt);
            tempData.push(isFirstRow);
            tempData.push(incomeRowAlt);
            tempData.push(incomeRow);
            tempData.push(patientDiffRow);
            tempData.push(incomeDiffRow);

            this.tableData = tempData;
        },
        getDifference(val, altVal = 0) {
            if (altVal == 0) {
                return 0;
            }
            return (val - altVal) / altVal * 100;
        },
        formatPercent(val) {
            return this.$formatter.numberFormat(val) + '%';
        },
        getFormattedPeriod(date) {
            return this.$formatter.dateFormat(date, 'MMM. YY');
        },
        getAlternateFilters(filters) {
            let tempFilters = {...filters};
            tempFilters.date_start = filters.alt_date_start;
            tempFilters.date_end = filters.alt_date_end;
            return tempFilters;
        },
        getPayments(filters) {
            return this.elasticSearchClient.getAggregations(this.paymentIndex, {
                size: 0,
                query: {
                    bool: {
                        filter: [
                            ...this.getPaymentsRequestFilter(filters),
                            {
                                terms: {
                                    clinic_id: filters.clinic
                                }
                            }
                        ]
                    }
                },
                aggs: {
                    aggr_group: {
                        terms: {
                            size: 20,
                            field: 'clinic_id'
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
            })
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
        getAppointments(filters) {
            return this.elasticSearchClient.getAggregations(this.appointmentIndex, {
                size: 0,
                query: {
                    bool: {
                        filter: [
                            ...this.getAppointmentsRequestFilter(filters),
                            {
                                terms: {
                                    clinic_id: filters.clinic
                                }
                            }
                        ]
                    }
                },
                aggs: {
                    aggr_group: {
                        terms: {
                            size: 20,
                            field: 'clinic_id'
                        },
                        aggs: {
                            is_first: {
                                sum: {
                                    field: 'is_first'
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
        exportExcel() {
            let columns = [
                {name: ' '},
                {name: ' '},
                ...this.clinicsToDisplay.map(c => {
                    return {name: c.value};
                })
            ];
            let rows = this.tableData.map(row => {
                let rowKeys = Object.keys(row);
                let result = [];
                rowKeys.forEach(key => {
                    result.push(row[key]);
                });
                return result;
            });

            fileGenerator.exportPlain(rows, columns).then((book) => {
                book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), __('Планирование') + '.xlsx');
                }).catch((err) => {
                    console.error(err);
                    this.$error(__('Не удалось сохранить отчет'));
                });
            });
        },
        exportPDF() {
            let settings = printer.docSettings;
            settings.styles += `
                @page {size: landscape}
                .table {font-size: 10px}
            `;

            printer.printComponent(ComparePrint, {
                tableData: this.tableData,
                clinicsToDisplay: this.clinicsToDisplay,
                clinicLabelMap: this.clinicLabelMap,
            }, null, settings);
        }
    }
}
</script>
