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
            />
        </section>
        <section
            v-if="tableData.length !== 0"
            class="pt-0"
        >
            <data-table
                :table-data="tableData"
                :specializations="specializations"
                @export="exportExcel"
            />
        </section>
    </div>
</template>
<script>
import ReportFilter from './TableFilter';
import DataTable from './Table';
import QueryHelper from './mixins/query-helper'
import specializationGenerator from '@/components/reports/generators/finance/doctor-specializationV2';
import FileSaver from 'file-saver';
import PaymentDestinationRepository from "@/repositories/service/payment-destination";
import SpecializationRepository from "../../../../repositories/specialization";

export default {
    components: {
        ReportFilter,
        DataTable,
    },
    mixins: [
        QueryHelper,
    ],
    props: {
        specializations: Array,
        informationSources: Array,
        elasticSearchClient: Object,
        callCenterSlicesIndex: String,
        incomePaymentsIndex: String,
    },
    data() {
        return {
            reportData: [],
            tableData: [],
            totalForYearAgo: [],
            selectedDatePeriod: {},
            selectedFilters: {},
            paymentDestinationsForReport: [],
            specs: new SpecializationRepository(),
        }
    },
    methods: {
        getReportData(updates) {
            this.filters = updates;
            if (this.filters.specialization.length === 0) {
                return this.$error(__('Выберите специализацию для поиска'));
            }
            this.loading = true;
            this.setupFilters();
            this.getPaymentDestinationsForReport().then((paymentDestinations) => {
                this.fetchData(paymentDestinations);
            })
        },
        getPaymentDestinationsForReport(filters = []) {
            let paymentDestinationsForReport = new PaymentDestinationRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            })
            paymentDestinationsForReport.setFilters(filters);
            return paymentDestinationsForReport.fetchList().then(response => {
                return response.filter(item => item.include_in_specialization_report).map(item => item.id);
            });
        },

        updateSpecializations(filters) {
            this.$emit('changed-specializations', filters)
        },

        setupFilters() {
            this.selectedFilters = this.prepareFilters();

            this.selectedDatePeriod.date_start = this.selectedFilters.date_start;
            this.selectedDatePeriod.date_end = this.selectedFilters.date_end;
        },

        fetchData(paymentDestinations) {
            this.fetchIncomePayments({...this.selectedFilters, payment_destination: paymentDestinations})
                .then(result => {
                    this.reportData = result
                    this.loading = false
                    this.generateTableData(this.reportData, 'selected_period');
                })
        },

        generateTableData(reportData, period) {
            if (period === 'selected_period') {
                this.tableData = this.generateTableRows(reportData);
                this.total = this.calculateTotal(this.tableData);
                this.tableData.push(this.total);
            }
        },

        generateTableRows(reportData) {
            let result = [];
            let doctors = this.getDoctors(reportData);
            for (let doctor of doctors) {
                doctor.first_count = this.getFirst(reportData,doctor.id);
                doctor.second_count = this.getSecond(reportData,doctor.id);
                doctor.secondPercentage = (doctor.first_count + doctor.second_count) ? (doctor.second_count/(doctor.first_count + doctor.second_count)*100).toFixed(1) : 0
                doctor.totalConsultationPayments = this.getConsultationsPayments(reportData,doctor.id)
                doctor.totalAnalisysPayments = this.getAnalyzesPayments(reportData,doctor.id)
                doctor.totalSonarsPayments = this.getSonarsPayments(reportData,doctor.id)
                doctor.totalTreatmentsPayments = this.getTreatmentsPayments(reportData,doctor.id)
                doctor.totalXrayDiagnosticsPayments = this.getTotalXrayDiagnosticsPayments(reportData,doctor.id)
                doctor.total = doctor.totalConsultationPayments + doctor.totalXrayDiagnosticsPayments + doctor.totalAnalisysPayments + doctor.totalSonarsPayments + doctor.totalTreatmentsPayments
                doctor.averageConsultationsExploration = (doctor.first_count + doctor.second_count) ? ((doctor.total - doctor.totalTreatmentsPayments)/(doctor.first_count + doctor.second_count)).toFixed(1) : 0
                doctor.averageConsultations = (doctor.totalConsultationPayments/(doctor.total)*100).toFixed(1)
                doctor.averageDiagnostics = (doctor.totalXrayDiagnosticsPayments/(doctor.total)*100).toFixed(1)
                doctor.averageAnalizys = (doctor.totalAnalisysPayments/(doctor.total)*100).toFixed(1)
                doctor.averageSonars = (doctor.totalSonarsPayments/(doctor.total)*100).toFixed(1)
                doctor.averageTreatments = (doctor.totalTreatmentsPayments/(doctor.total)*100).toFixed(1)
            }
            let tableRow = {};
            for (let doctor of doctors){
                tableRow = {};
                tableRow = doctor;
                result.push(tableRow)
            }

            return result;
        },

        calculateTotal(tableData) {
            let total = {
                is_total: true,
            };
            _.each(tableData, tableRow => {
                _.each(tableRow, (value, key) => {
                    if (! total[key]) {
                        total[key] = +value;
                    } else {
                        total[key] += +value;
                    }
                })
            })
            total.name = __('Итого')
            total.secondPercentage = (total.first_count + total.second_count) ? (total.second_count/(total.first_count + total.second_count)*100).toFixed(1) : 0
            total.averageConsultationsExploration = (total.first_count + total.second_count) ? ((total.total - total.totalTreatmentsPayments)/(total.first_count + total.second_count)).toFixed(1) : 0
            total.averageConsultations = (total.totalConsultationPayments/(total.total)*100).toFixed(1)
            total.averageDiagnostics = (total.totalXrayDiagnosticsPayments/(total.total)*100).toFixed(1)
            total.averageAnalizys = (total.totalAnalisysPayments/(total.total)*100).toFixed(1)
            total.averageSonars = (total.totalSonarsPayments/(total.total)*100).toFixed(1)
            total.averageTreatments = (total.totalTreatmentsPayments/(total.total)*100).toFixed(1)
            return total;
        },
        exportExcel() {
            this.loading = true;
            specializationGenerator(this.tableData,this.selectedFilters).then((book) => {
                book.xlsx.writeBuffer().then((buffer) => {
                    this.getFileName().then((fileName) => {
                        FileSaver.saveAs(new Blob([buffer]), fileName)
                        this.loading = false;
                    });
                }).catch((err) => {
                    console.error(err);
                    this.$error(__('Не удалось сохранить отчет'));
                });
            });
        },
        getFileName() {
            return this.getSpecializationNames().then(list => {
                return Promise.resolve(list.join('_') + ".xlsx");
            }) ;
        },
        getSpecializations() {
            return this.specs.fetchList();
        },
        getSpecializationNames() {
            return this.getSpecializations().then(list => {
                let specializations = list.filter(item => this.filters.specialization.indexOf(item.id) !== -1)
                    .map(item => item.short_name);
                return Promise.resolve(specializations);
            })
        },
    }
}
</script>
