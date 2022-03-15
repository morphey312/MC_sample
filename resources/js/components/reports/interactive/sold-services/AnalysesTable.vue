<template>
    <div v-loading="loading">
        <section class="grey">
            <analyses-filter
                ref="filter"
                :initial-state="filters"
                :clinics="clinics"
                :laboratories="laboratories"
                @changed="getReportData"/>
        </section>
        <section class="pt-0" v-if="tableData.length != 0">
            <analyses-table
                :table-data="tableData"
                @export="exportExcel"
            />
        </section>
    </div>
</template>

<script>
import AnalysesFilter from "./analyses/TableFilter";
import AnalysesTable from "./analyses/Table";
import ChartMixin from './mixins/chart';
import generator from "./generators/analyses";
import FileSaver from 'file-saver';

export default {
    components: {
        AnalysesFilter,
        AnalysesTable,
    },
    mixins: [
        ChartMixin
    ],
    props: {
        elasticSearchClient: Object,
        reportIndex: String,
        clinics: Array,
    },
    data() {
        return {
            analyses: [],
        }
    },
    methods: {
        getReportData(updates) {
            this.changeFilters(updates);
            let filters = this.prepareFilters();

            this.fetchData(filters).then(response => {
                this.tableData = this.prepareDataRows(generator(response, filters.laboratory));
                this.loading = false;
            });

        },
        prepareDataRows(data) {
            let rows = [];
            Object.keys(data.byAnalysis).forEach(key => {
                let clinicRow = data.byAnalysis[key];

                Object.keys(clinicRow.byClinic).sort().forEach((clinicKey, index) => {
                    let clinic = clinicRow.byClinic[clinicKey];

                    rows.push({
                        clinic: this.getName(clinicKey),
                        analysis_code: clinicRow.analysis_code,
                        analysis_code_clinic: clinicRow.analysis_code_clinic,
                        laboratory: clinicRow.laboratory,
                        analysis: clinicRow.analysis,
                        count: clinic.count,
                        payed: clinic.sum_sold,
                        payments: clinic.payed ? clinic.payed : 0,
                    });
                });
            });

            let summary = {analysis: __('Суммарно'), count: 0, payed: 0, payments: 0, isTableSummary: true};

            Object.keys(data.byAnalysis).sort().forEach((key, index) => {
                let specialization = data.byAnalysis[key];

                summary.count += specialization.count;
                summary.payed += specialization.sum_sold;
                summary.payments += specialization.payed;
            });

            rows.push(summary);
            return rows;
        },
        exportExcel() {
            this.loading = true;
            let book = generator(this.tableData, null, 'export');
            book.xlsx.writeBuffer().then((buffer) => {
                FileSaver.saveAs(new Blob([buffer]), __('Отчет проданыые услуги - анализы.xlsx'))
                this.loading = false;
            }).catch((err) => {
                console.error(err);
                this.$error(__('Не удалось сохранить отчет'));
                this.loading = false;
            });
        },
        getName(id) {
            let clinic = this.clinics.find(row => row.id == id);
            return clinic ? clinic.value : null;
        },
        getSpecializationName(id) {
            let specialization = this.specializations.find(row => row.id == id);
            return specialization ? specialization.short_name : null;
        }
    }
}
</script>
