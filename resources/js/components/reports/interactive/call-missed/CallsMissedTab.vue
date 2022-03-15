<template>
    <div v-loading="loading">
        <section class="grey">
            <missed-calls-filter
                ref="filter"
                :initial-state="filters"
                :clinics="clinics"
                :queue="queue"
                @changed="getReportData"/>
        </section>
        <section class="pt-0" v-if="tableData.length != 0">
            <calls-queues-sub-header
                :chosenQueues="chosenQueues"
            />
            <missed-calls-table
                :table-data="tableData"
                @export="exportExcel"
            />
        </section>
    </div>
</template>

<script>
import MissedCallsFilter from "./filters/MissedTableFilter";
import MissedCallsTable from "./data-tables/MissedCalls";
import ChartMixin from './mixins/chart';
import generator from "./generators/missed-calls";
import FileSaver from 'file-saver';
import CallsQueuesSubHeader from "./CallsQueuesSubHeader.vue";

export default {
    components: {
        CallsQueuesSubHeader,
        MissedCallsFilter,
        MissedCallsTable,
    },
    mixins: [
        ChartMixin
    ],
    props: {
        elasticSearchClient: Object,
        reportIndex: String,
        clinics: Array,
        queue: Array,
    },
    data() {
        return {
            missedCalls: [],
            loading: false,
            chosenQueues: '',
        }
    },
    methods: {
        getReportData(updates) {
            this.changeFilters(updates);
            let filters = this.prepareFilters();
            this.chosenQueues = filters.queue.join(', ');

            this.fetchData(filters).then(response => {
                this.tableData = this.prepareDataRows(generator(response));
                this.loading = false;
            });
        },
        prepareDataRows(data) {
            let rows = [];
            Object.keys(data.byDate).forEach(key => {
                let rowResult = data.byDate[key];

                rows.push({
                    started_at: rowResult.started_at,
                    countCalls: rowResult.countCalls,
                    countMissed: rowResult.countMissed,
                    percentMissed: rowResult.percentMissed,
                });
            });
            rows.sort((a, b) => {
                let da = new Date(a.started_at),
                    db = new Date(b.started_at);
                return da - db;
            });

            let summary = {started_at: __('Всего'), countCalls: 0, countMissed: 0, percentMissed: 0, isTableSummary: true};

            Object.keys(data.byDate).forEach((key, index) => {
                let missed = data.byDate[key];
                summary.countCalls += missed.countCalls;
                summary.countMissed += missed.countMissed;
            });
            summary.percentMissed += Math.round(summary.countMissed * 10000 / summary.countCalls) / 100;

            rows.push(summary);
            return rows;
        },
        exportExcel() {
            this.loading = true;
            let book = generator(this.tableData, 'export');
            book.xlsx.writeBuffer().then((buffer) => {
                FileSaver.saveAs(new Blob([buffer]), __('Отчет по пропущенным звонкам.xlsx'))
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
