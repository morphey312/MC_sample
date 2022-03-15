<template>
    <div v-loading="loading">
        <section class="grey">
            <report-filter 
                ref="filter"
                :initial-state="filters"
                @changed="getReportData"/>
        </section>
        <section class="pt-0" v-if="tableData.length != 0">
            <data-table 
               :table-data="tableData"
               @export="exportExcel"
            />
        </section>
    </div>
</template>
<script>
import ReportFilter from './TableFilter.vue';
import DataTable from './Table.vue';
import fileGenerator from '@/components/reports/generators/finance/redirect';
import generator from './generator';
import FileSaver from 'file-saver';
import ChartMixin from './mixins/chart';

export default {
    mixins: [
        ChartMixin,
    ],
    components: {
        ReportFilter,
        DataTable,
    },
    methods: {
        getReportData(filters) {
            this.getData(filters).then(data => {
                this.tableData = data;
                this.loading = false;
            });
        },
        exportExcel() {
            this.loading = true;
            let book = generator(this.tableData, null, 'export');
            book.xlsx.writeBuffer().then((buffer) => {
                FileSaver.saveAs(new Blob([buffer]), __('Перенаправления.xlsx'))
                this.loading = false;
            }).catch((err) => {
                console.error(err);
                this.$error(__('Не удалось сохранить отчет'));
                this.loading = false;
            });
        },
    }
}
</script>