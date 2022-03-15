<template>
    <div v-loading="loading">
        <section class="grey">
            <report-filter
                ref="filter"
                :initial-state="filters"
                :clinics="clinics"
                :specializations="specializations"
                :information-sources="informationSources"
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
                :cities="cities"
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
import MarketingCitiesGenerator from './generator'

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
            tableData: [],
            cities: [],
            selectedSpecializations: [],
            selectedInformationSources: [],
            totalForSelectedPeriod: [],
            selectedDatePeriod: {},
            selectedFilters: {},
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

        },

        fetchData() {
            this.fetchCallCenterData(this.selectedFilters)
                .then(result => {
                    let reportData = {};

                    const callCenterSelectedPeriodData = this.getReportDataByPeriod(result, this.selectedDatePeriod.date_start, this.selectedDatePeriod.date_end);

                    reportData.calls = this.getCalls(callCenterSelectedPeriodData);
                    reportData.appointments = this.getAppointments(callCenterSelectedPeriodData);
                    reportData.incomes = this.getIncomes(callCenterSelectedPeriodData);
                    reportData.treatments = this.getTreatments(callCenterSelectedPeriodData);

                    this.cities = this.getCities(callCenterSelectedPeriodData);
                    this.reportData = this.sortReportDataBySource(reportData);
                    this.generateTableData(this.reportData);
                    this.loading = false;

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
                    let patient_location = null;
                    let specializationId = '';
                    if (_.has(value, 'patient_location') && value.patient_location.toLowerCase()) {
                        patient_location = value.patient_location.toLowerCase();
                    }

                    if (_.has(value, 'specialization_id') && value.specialization_id) {
                        specializationId = value.specialization_id;
                    }
                    if (_.has(value, 'appointment_card_specialization_id') && value.appointment_card_specialization_id) {
                        specializationId = value.appointment_card_specialization_id;
                    }
                    if (patient_location && specializationId) {
                        if (! _.has(result, patient_location)) {
                            result[patient_location] = {};
                        }

                        if (! _.has(result[patient_location], specializationId)) {
                            result[patient_location][specializationId] = {};
                        }

                        if (! _.has(result[patient_location][specializationId], reportDataKey)) {
                            result[patient_location][specializationId][reportDataKey] = [];
                        }

                        value.patient_location = patient_location;
                        result[patient_location][specializationId][reportDataKey].push(value);
                    }
                });
            });
            return result;
        },

        generateTableData(reportData) {
            this.tableData = this.generateTableRows(reportData);
            this.calculateTotalAmountByRowTable();
            this.totalForSelectedPeriod = this.calculateTotal(this.tableData);
            this.tableData.push(this.totalForSelectedPeriod);

        },

        generateTableRows(reportData) {
            let result = [];
            _.each(reportData, (city, cityKey) => {
                let tableRow = {}
                tableRow.patient_location = cityKey;
                _.each(city, (specialization, specializationKey) => {
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
                    }})
                }
            })
        },


        calculateTotal(tableData) {
            let total = {
                is_total: true
            };
            _.each(tableData, tableRow => {
                _.each(tableRow, (value, key) => {
                    if (key !== 'media_type' && key !== 'is_total_by_media_type' && key !== 'sources') {
                        if (! total[key]) {
                            total[key] = value;
                        } else {
                            total[key] += value;
                        }
                    }
                })
            })

            return total;
        },

        exportExcel() {
            this.loading = true;
            MarketingCitiesGenerator(this.tableData,this.specializations).then((book) => {
                book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), __('Отчет - Маркетинг - Города.xlsx'))
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
