<template>
    <div v-loading="loading">
        <section class="grey">
            <report-filter
                ref="filter"
                :initial-state="filters"
                :clinics="clinics"
                :specializations="specializations"
                :information-sources="informationSources"
                :media-types="mediaTypes"
                @changed="getReportData"
                @changed-specializations="updateSpecializations"
                @changed-information-sources="updateInformationSources"
            />
        </section>
        <section
            v-if="tableData.length !== 0"
            class="pt-0"
        >
            <data-table
                :table-data="tableData"
                :information-sources="informationSources"
                :specializations="specializations"
                :selected-specializations="selectedSpecializations"
                :media-types="mediaTypes"
                @export="exportExcel"
            />
        </section>
    </div>
</template>
<script>
import ReportFilter from './TableFilter';
import QueryHelper from './mixins/query-helper'
import DataTable from './Table';
import FileSaver from 'file-saver';
import ExcelGenerator from './mixins/excel-generator'

export default {
    components: {
        ReportFilter,
        DataTable,
    },
    mixins: [
        QueryHelper,
    ],
    props: {
        clinics: Array,
        specializations: Array,
        informationSources: Array,
        mediaTypes: Array,
        elasticSearchClient: Object,
        callCenterSlicesIndex: String,
        incomePaymentsIndex: String,
    },
    data() {
        return {
            reportData: {},
            reportDataMonthAgo: {},
            reportDataYearAgo: {},
            tableData: [],
            tableDataMonthAgo: [],
            tableDataYearAgo: [],
            selectedSpecializations: [],
            selectedInformationSources: [],
            totalForSelectedPeriod: [],
            totalForMonthAgo: [],
            totalForYearAgo: [],
            selectedDatePeriod: {},
            selectedFilters: {},
            paymentsCurrentPeriod: {}
        }
    },
    methods: {
        getReportData(updates) {
            this.filters = updates;
            if (! this.filters.clinic) {
                return this.$error(__('Выберите клинику для поиска'));
            }
            this.loading = true;
            this.setupFilters();
            this.fetchData();
        },

        updateSpecializations(filters) {
            this.$emit('changed-specializations', filters)
        },

        updateInformationSources(filters) {
            this.$emit('changed-information-sources', filters)
        },

        setupFilters() {
            this.selectedFilters = this.prepareFilters();

            this.selectedSpecializations = (this.selectedFilters.specialization && this.selectedFilters.specialization.length > 0)
                ? this.selectedFilters.specialization
                : this.specializations.map(specialization => {
                    return specialization.id;
                });
            this.selectedFilters.specialization = this.selectedSpecializations;

            this.selectedInformationSources = this.getSelectedInformationSources(this.selectedFilters);
            this.selectedFilters.information_sources = this.selectedInformationSources;

            this.selectedDatePeriod.date_start = this.selectedFilters.date_start;
            this.selectedDatePeriod.date_end = this.selectedFilters.date_end;

            this.selectedFilters.date_start_month_ago = this.$moment(this.selectedFilters.date_start).subtract(1, 'months').format('YYYY-MM-01');
            this.selectedFilters.date_end_month_ago = this.$moment(this.selectedFilters.date_end).subtract(1, 'months').endOf('month').format('YYYY-MM-DD');

            this.selectedFilters.date_start_year_ago = this.$moment(this.selectedFilters.date_start).subtract(1, 'year').format('YYYY-MM-01');
            this.selectedFilters.date_end_year_ago = this.$moment(this.selectedFilters.date_end).subtract(1, 'year').endOf('month').format('YYYY-MM-DD');
        },

        fetchData() {
            this.fetchCallCenterData(this.selectedFilters)
                .then(result => {
                    let reportData = {};
                    let reportDataMonthAgo = {};
                    let reportDataYearAgo = {};

                    const callCenterSelectedPeriodData = this.getReportDataByPeriod(result, this.selectedDatePeriod.date_start, this.selectedDatePeriod.date_end);
                    const callCenterMonthAgoData = this.getReportDataByPeriod(result, this.selectedFilters.date_start_month_ago, this.selectedFilters.date_end_month_ago);
                    const callCenterYearAgoData = this.getReportDataByPeriod(result, this.selectedFilters.date_start_year_ago, this.selectedFilters.date_end_year_ago);

                    reportData.calls = this.getCalls(callCenterSelectedPeriodData);
                    reportData.appointments = this.getAppointments(callCenterSelectedPeriodData);
                    reportData.incomes = this.getIncomes(callCenterSelectedPeriodData);
                    reportData.treatments = this.getTreatments(callCenterSelectedPeriodData);

                    reportDataMonthAgo.calls = this.getCalls(callCenterMonthAgoData);
                    reportDataMonthAgo.appointments = this.getAppointments(callCenterMonthAgoData);
                    reportDataMonthAgo.incomes = this.getIncomes(callCenterMonthAgoData);
                    reportDataMonthAgo.treatments = this.getTreatments(callCenterMonthAgoData);

                    reportDataYearAgo.calls = this.getCalls(callCenterYearAgoData);
                    reportDataYearAgo.appointments = this.getAppointments(callCenterYearAgoData);
                    reportDataYearAgo.incomes = this.getIncomes(callCenterYearAgoData);
                    reportDataYearAgo.treatments = this.getTreatments(callCenterYearAgoData);

                    this.fetchIncomePayments(this.selectedFilters)
                        .then(result => {
                            reportData.payments = this.paymentsCurrentPeriod = this.getReportDataByPeriod(result, this.selectedDatePeriod.date_start, this.selectedDatePeriod.date_end, true);
                            reportDataMonthAgo.payments = this.getReportDataByPeriod(result, this.selectedFilters.date_start_month_ago, this.selectedFilters.date_end_month_ago, true);
                            reportDataYearAgo.payments = this.getReportDataByPeriod(result, this.selectedFilters.date_start_year_ago, this.selectedFilters.date_end_year_ago, true);
                            this.reportData = this.sortReportDataBySource(reportData);
                            this.reportDataMonthAgo = this.sortReportDataBySource(reportDataMonthAgo);
                            this.reportDataYearAgo = this.sortReportDataBySource(reportDataYearAgo);
                            this.generateTableData(this.reportData, 'selected_period');
                            this.generateTableData(this.reportDataMonthAgo, 'month_ago');
                            this.generateTableData(this.reportDataYearAgo, 'year_ago');
                            this.loading = false;
                        });
                })
        },

        getReportDataByPeriod(reportData, dateStart, dateEnd, isPayments = false) {
            let result = [];

            if (isPayments) {
                _.each(reportData, value => {
                    if (this.$moment(value.created_at) >= this.$moment(dateStart) && this.$moment(value.created_at) <= this.$moment(dateEnd)) {
                        result.push(value);
                    }
                });
            } else {
                _.each(reportData, value => {
                    if (this.$moment(value.date) >= this.$moment(dateStart) && this.$moment(value.date) <= this.$moment(dateEnd)) {
                        result.push(value);
                    }
                });
            }

            return result;
        },

        sortReportDataBySource(reportData) {
            let result = {};

            _.each(reportData, (data, reportDataKey) => {
                _.each(data, value => {
                    let specializationId = '';
                    let sourceId = value.patient_source_id;

                    if (_.has(value, 'specialization_id') && value.specialization_id) {
                        specializationId = value.specialization_id;
                    }

                    if (_.has(value, 'appointment_card_specialization_id') && value.appointment_card_specialization_id) {
                        specializationId = value.appointment_card_specialization_id;
                    }

                    if (sourceId && specializationId) {
                        if (! _.has(result, sourceId)) {
                            result[sourceId] = {};
                        }

                        if (! _.has(result[sourceId], specializationId)) {
                            result[sourceId][specializationId] = {};
                        }

                        if (! _.has(result[sourceId][specializationId], reportDataKey)) {
                            result[sourceId][specializationId][reportDataKey] = [];
                        }

                        value.source_id = sourceId;
                        value.media_type = _.find(this.informationSources, ['id', sourceId]) ? _.find(this.informationSources, ['id', sourceId]).media_type : 'none';
                        result[sourceId][specializationId][reportDataKey].push(value);
                    }
                });
            });

            return result;
        },

        generateTableData(reportData, period) {
            if (period === 'selected_period') {
                this.tableData = this.generateTableRows(reportData);
                this.tableData = this.calculateTotalAmountByMediaType(this.tableData);
                this.tableData = this.sortTableData(this.tableData);
                this.totalForSelectedPeriod = this.calculateTotal(this.tableData);
                this.tableData.push(this.totalForSelectedPeriod);
            }

            if (period === 'month_ago') {
                let tableDataMonthAgo = this.generateTableRows(reportData);
                tableDataMonthAgo = this.calculateTotalAmountByMediaType(tableDataMonthAgo);
                tableDataMonthAgo = this.sortTableData(tableDataMonthAgo);
                this.totalForMonthAgo = this.calculateTotal(tableDataMonthAgo);
                delete this.totalForMonthAgo.is_total;
                this.totalForMonthAgo.is_total_for_month_ago = true;
                this.totalForMonthAgo.date_start = this.selectedFilters.date_start_month_ago;
                this.totalForMonthAgo.date_end = this.selectedFilters.date_end_month_ago;
            }

            if (period === 'year_ago') {
                let tableYearMonthAgo = this.generateTableRows(reportData);
                tableYearMonthAgo = this.calculateTotalAmountByMediaType(tableYearMonthAgo);
                tableYearMonthAgo = this.sortTableData(tableYearMonthAgo);
                this.totalForYearAgo = this.calculateTotal(tableYearMonthAgo);
                delete this.totalForYearAgo.is_total;
                this.totalForYearAgo.is_total_for_year_ago = true;
                this.totalForYearAgo.date_start = this.selectedFilters.date_start_year_ago;
                this.totalForYearAgo.date_end = this.selectedFilters.date_end_year_ago;

                this.calculatePredicts();
                this.tableData.push(this.totalForMonthAgo);
                this.tableData.push(this.totalForYearAgo);
                this.calculateTotalAmountByRowTable();
                this.calculatePercentagesByPredicts(this.totalForMonthAgo);
                this.calculatePercentagesByPredicts(this.totalForYearAgo);
                this.calculateTotalPercentagesBySpecializations();
                this.calculatePredictsByRowTable();
            }
        },

        generateTableRows(reportData) {
            let result = [];

            _.each(reportData, (informationSource, informationSourceKey) => {
                let tableRow = {};
                tableRow.source = parseInt(informationSourceKey, 10);
                tableRow.media_type = _.find(this.informationSources, ['id', tableRow.source]) ? _.find(this.informationSources, ['id', tableRow.source]).media_type : 'none';
                _.each(informationSource, (specialization, specializationKey) => {
                    if (! _.has(tableRow, `calls_${specializationKey}`)) {
                        tableRow[`calls_${specializationKey}`] = specialization.calls
                            ? specialization.calls.length
                            : 0;
                    } else {
                        tableRow[`calls_${specializationKey}`] += specialization.calls
                            ? specialization.calls.length
                            : 0;
                    }

                    if (! _.has(tableRow, `appointments_${specializationKey}`)) {
                        tableRow[`appointments_${specializationKey}`] = specialization.appointments
                            ? specialization.appointments.length
                            : 0;
                    } else {
                        tableRow[`appointments_${specializationKey}`] += specialization.appointments
                            ? specialization.appointments.length
                            : 0;
                    }

                    if (! _.has(tableRow, `incomes_${specializationKey}`)) {
                        tableRow[`incomes_${specializationKey}`] = specialization.incomes
                            ? specialization.incomes.length
                            : 0;
                    } else {
                        tableRow[`incomes_${specializationKey}`] += specialization.incomes
                            ? specialization.incomes.length
                            : 0;
                    }

                    if (! _.has(tableRow, `treatments_${specializationKey}`)) {
                        tableRow[`treatments_${specializationKey}`] = specialization.treatments
                            ? specialization.treatments.length
                            : 0;
                    } else {
                        tableRow[`treatments_${specializationKey}`] += specialization.treatments
                            ? specialization.treatments.length
                            : 0;
                    }

                    if (! _.has(tableRow, `payments_${specializationKey}`)) {
                        tableRow[`payments_${specializationKey}`] = specialization.payments
                            ? specialization.payments.reduce(function (prev, next) {
                                if (next.type === 'expense') {
                                    return prev - next.payed_amount
                                } else {
                                    return prev + next.payed_amount
                                }
                            }, 0)
                            : 0;
                    } else {
                        tableRow[`payments_${specializationKey}`] += specialization.payments
                            ? specialization.payments.reduce(function (prev, next) {
                                if (next.type === 'expense') {
                                    return prev - next.payed_amount
                                } else {
                                    return prev + next.payed_amount
                                }
                            }, 0)
                            : 0;
                    }
                });

                result.push(tableRow);
            });

            return result;
        },

        getSelectedInformationSources(filters) {
            let selectedInformationSources = (filters.information_sources && filters.information_sources.length > 0)
                ? filters.information_sources
                : this.informationSources.map(specialization => {
                    return specialization.id;
                });

            const selectedMediaTypes = (filters.media_types && filters.media_types.length > 0)
                ? filters.media_types
                : this.mediaTypes.map(mediaType => {
                    return mediaType.id;
                })

            return selectedInformationSources.filter(selectedInformationSource => {
                return this.checkIfInformationSourceIsOfMediaTypes(selectedInformationSource, selectedMediaTypes);
            })
        },

        checkIfInformationSourceIsOfMediaTypes(selectedInformationSourceId, selectedMediaTypeIds) {
            const selectedInformationSource = _.find(this.informationSources, ['id', selectedInformationSourceId]);

            if (selectedInformationSource && selectedInformationSource.media_type) {
                return selectedMediaTypeIds.indexOf(selectedInformationSource.media_type) !== -1;
            }

            return false;
        },

        calculateTotalAmountByMediaType(reportData) {
            _.each(this.mediaTypes, mediaType => {
                let totalAmount = {};
                _.each(reportData, tableRow => {
                    if (mediaType.id === tableRow.media_type) {
                        _.each(tableRow, (value, key) => {
                            totalAmount.is_total_by_media_type = true;

                            if (key !== 'media_type') {
                                if (! totalAmount[key]) {
                                    totalAmount[key] = value;
                                } else {
                                    totalAmount[key] += value;
                                }
                            }

                            totalAmount.media_type = totalAmount.media_type || tableRow.media_type;
                        })
                    }
                })

                if (! _.isEmpty(totalAmount)) {
                    delete totalAmount.source;
                    reportData.push(totalAmount);
                }
            })

            return reportData;
        },

        sortTableData(tableData) {
            tableData.sort((a, b) => {
                return a.media_type - b.media_type;
            })

            return tableData;
        },

        calculateTotal(tableData) {
            let total = {
                is_total: true
            };

            _.each(tableData, tableRow => {
                if (! tableRow.is_total_by_media_type) {
                    _.each(tableRow, (value, key) => {
                        if (key !== 'media_type' && key !== 'is_total_by_media_type' && key !== 'sources') {
                            if (! total[key]) {
                                total[key] = value;
                            } else {
                                total[key] += value;
                            }
                        }
                    })
                }
            })

            return total;
        },

        calculateTotalAmountByRowTable() {
            _.each(this.tableData, (tableRow, key) => {
                if (! tableRow.is_total_percentages) {
                    _.each(tableRow, (value, columnName) => {{
                        if (columnName.indexOf('calls') !== -1) {
                            if (! this.tableData[key].total_calls) {
                                this.tableData[key].total_calls = value;
                            } else {
                                this.tableData[key].total_calls += value;
                            }
                        }

                        if (columnName.indexOf('appointments') !== -1) {
                            if (! this.tableData[key].total_appointments) {
                                this.tableData[key].total_appointments = value;
                            } else {
                                this.tableData[key].total_appointments += value;
                            }
                        }

                        if (columnName.indexOf('incomes') !== -1) {
                            if (! this.tableData[key].total_incomes) {
                                this.tableData[key].total_incomes = value;
                            } else {
                                this.tableData[key].total_incomes += value;
                            }
                        }

                        if (columnName.indexOf('treatments') !== -1) {
                            if (! this.tableData[key].total_treatments) {
                                this.tableData[key].total_treatments = value;
                            } else {
                                this.tableData[key].total_treatments += value;
                            }
                        }

                        if (columnName.indexOf('payments') !== -1) {
                            if (! this.tableData[key].total_payments) {
                                this.tableData[key].total_payments = value;
                            } else {
                                this.tableData[key].total_payments += value;
                            }

                            if (this.tableData[key].is_total) {
                                this.tableData[key].total_payments = this.calcPayed(this.paymentsCurrentPeriod);
                            }
                        }
                    }})
                }
            })
        },

        calculateTotalPercentagesBySpecializations() {
            let percentRow = {
                is_total_percentages: true,
            };

            _.each(this.totalForSelectedPeriod, (value, key) => {
                if (key.indexOf('appointments') !== -1) {
                    const specializationId = key.split('_')[1];
                    const callsForCurrentSpecialization = this.totalForSelectedPeriod[`calls_${specializationId}`];
                    percentRow[key] = (callsForCurrentSpecialization ? Math.round((value / callsForCurrentSpecialization) * 100) : 0) + '%';
                }

                if (key.indexOf('incomes') !== -1) {
                    const specializationId = key.split('_')[1];
                    const appointmentsForCurrentSpecialization = this.totalForSelectedPeriod[`appointments_${specializationId}`];
                    percentRow[key] = (appointmentsForCurrentSpecialization ? Math.round((value / appointmentsForCurrentSpecialization) * 100) : 0) + '%';
                }

                if (key.indexOf('treatments') !== -1) {
                    const specializationId = key.split('_')[1];
                    const incomesForCurrentSpecialization = this.totalForSelectedPeriod[`incomes_${specializationId}`];
                    percentRow[key] = (incomesForCurrentSpecialization ? Math.round((value / incomesForCurrentSpecialization) * 100) : 0) + '%';
                }

                if (key.indexOf('total') !== -1) {
                    percentRow['total_appointments'] = (this.totalForSelectedPeriod.total_calls ? Math.round((this.totalForSelectedPeriod.total_appointments / this.totalForSelectedPeriod.total_calls) * 100) : 0) + '%';
                    percentRow['total_incomes'] = (this.totalForSelectedPeriod.total_appointments ? Math.round((this.totalForSelectedPeriod.total_incomes / this.totalForSelectedPeriod.total_appointments) * 100) : 0) + '%';
                    percentRow['total_treatments'] = (this.totalForSelectedPeriod.total_incomes ? Math.round((this.totalForSelectedPeriod.total_treatments / this.totalForSelectedPeriod.total_incomes) * 100) : 0) + '%';
                }
            })

            this.tableData.splice(_.findIndex(this.tableData, 'is_total') + 1, 0, percentRow);
        },

        calculatePredicts() {
            let predictsRow = {
                is_predicts: true,
            };

            const reportUploadDay = this.getReportUploadDay();
            // const numberDayOfSelectedDatePeriod = this.getNumberDayOfSelectedDatePeriod(); // для подсчета прогнозов по конечной дате фильтра, а не по количеству дней в месяце
            const numberDaysOfMonth = this.$moment(this.selectedDatePeriod.date_start, 'YYYY/MM').daysInMonth();

            _.each(this.totalForSelectedPeriod, (value, key) => {
                if (key !== 'is_total') {
                    predictsRow[key] = reportUploadDay ? Math.round((value / reportUploadDay) * numberDaysOfMonth) : 0;
                }
            })

            this.tableData.push(predictsRow);
        },

        getReportUploadDay() {
            const currentDate = this.$moment();
            const reportDateStart = this.$moment(this.selectedDatePeriod.date_start, 'YYYY/MM/DD');
            const reportDateEnd = this.$moment(this.selectedDatePeriod.date_end, 'YYYY/MM/DD');
            const reportMonth = reportDateStart.format('M');
            const currentMonth = currentDate.format('M');

            if (reportMonth === currentMonth) {
                const currentDay = currentDate.format('D');
                const reportStartDay = reportDateStart.format('D');
                return currentDay - reportStartDay;
            }

            return reportDateEnd.format('D');
        },

        getNumberDayOfSelectedDatePeriod() {
            const reportDateStart = this.$moment(this.selectedDatePeriod.date_start, 'YYYY-MM-DD');
            const reportDateEnd = this.$moment(this.selectedDatePeriod.date_end, 'YYYY-MM-DD');

            return reportDateEnd.diff(reportDateStart, 'days') + 1;
        },

        calculatePercentagesByPredicts(total) {
            let result = {
                is_percent_by_predict: true,
            };
            const predicts = _.find(this.tableData, 'is_predicts');

            _.each(total, (value, key) => {
                result[key] = (value ? Math.round(((predicts[key] || 0) / value) * 100) : 0) + '%';
            })

            if (result.is_total_for_month_ago) {
                this.tableData.splice(_.findIndex(this.tableData, 'is_total_for_month_ago') + 1, 0, result);
                delete result.is_total_for_month_ago;
            }

            if (result.is_total_for_year_ago) {
                this.tableData.splice(_.findIndex(this.tableData, 'is_total_for_year_ago') + 1, 0, result);
                delete result.is_total_for_year_ago;
            }
        },

        calculatePredictsByRowTable() {
            const reportUploadDay = this.getReportUploadDay();
            const numberDaysOfMonth = this.$moment(this.selectedDatePeriod.date_start, 'YYYY/MM').daysInMonth();

            _.each(this.tableData, (value, rowKey) => {
                if (
                    ! value.is_total_percentages
                    && ! value.is_predicts
                    && ! value.is_total_for_month_ago
                    && ! value.is_total_for_year_ago
                    && ! value.is_percent_by_predict
                ) {
                    this.tableData[rowKey].predict_calls = reportUploadDay ? Math.round((value.total_calls / reportUploadDay) * numberDaysOfMonth) : 0;
                    this.tableData[rowKey].predict_treatments = reportUploadDay ? Math.round((value.total_treatments / reportUploadDay) * numberDaysOfMonth) : 0;

                    if (value.is_total_by_media_type) {
                        const totalCalls = _.find(this.tableData, 'is_total').total_calls;
                        this.tableData[rowKey].predict_percent = ((value.total_calls / totalCalls) * 100).toFixed(2) + '%';
                    }
                }
            })
        },

        calcPayed(payments) {
            return payments.reduce((total, row) => {
                if (row.type === 'income') {
                    total += Number(row.payed_amount);
                }
                if (row.type === 'expense') {
                    total -= Number(row.payed_amount);
                }
                return total;
            }, 0);
        },

        exportExcel() {
            this.loading = true;
            ExcelGenerator(this.tableData, this.specializations, this.informationSources, this.mediaTypes).then((book) => {
                book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), __('Отчет - Маркетинг - Сводная Итого.xlsx'))
                    this.loading = false;
                });
            }).catch((err) => {
                console.error(err);
                this.$error(__('Не удалось сохранить отчет'));
            });
        },
    }
}
</script>
