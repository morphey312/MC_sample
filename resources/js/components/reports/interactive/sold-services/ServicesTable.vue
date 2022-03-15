<template>
    <div v-loading="loading">
        <section class="grey">
            <service-filter
                ref="filter"
                :initial-state="filters"
                :clinics="clinics"
                :specializations="specializations"
                @changed="getReportData"/>
        </section>
        <section class="pt-0" v-if="tableData.length != 0">
            <service-table
                :table-data="tableData"
                @export="exportExcel"
            />
        </section>
    </div>
</template>

<script>
import ServiceFilter from "./services/TableFilter";
import ServiceTable from "./services/Table";
import ChartMixin from './mixins/chart';
import generator from "./generators/services";
import FileSaver from 'file-saver';

export default {
    components: {
        ServiceFilter,
        ServiceTable,
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
            services: [],
        }
    },
    methods: {
        getReportData(updates) {
            this.changeFilters(updates);
            let filters = this.prepareFilters();

            this.fetchData(filters).then(response => {
                this.tableData = this.prepareDataRows(generator(response, filters.specialization));
                this.loading = false;
            });
        },
        prepareDataRows(data) {
            let rows = [];
            Object.keys(data.byService).forEach(key => {
                let service = data.byService[key];

                Object.keys(service.byClinic).sort().forEach((clinicKey, index) => {
                    let clinic = service.byClinic[clinicKey];
                    rows.push({
                        service: service.name,
                        clinic: this.getName(clinicKey),
                        specialization: this.getSpecializationName(service.specialization),
                        count: clinic.count ? clinic.count : 0,
                        payed: clinic.sum_sold,
                        payments: clinic.payed ? clinic.payed : 0,
                    });
                });
            });

            let summary = {service: __('Суммарно'), count: 0, payed: 0, payments: 0, isTableSummary: true};

            Object.keys(data.byService).sort().forEach((key, index) => {
                let specialization = data.byService[key];
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
                FileSaver.saveAs(new Blob([buffer]), __('Отчет проданыые услуги - услуги.xlsx'))
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
