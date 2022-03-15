<template>
    <div v-loading="loading">
        <section class="grey">
            <report-filter
                ref="filter"
                :initial-state="filters"
                :clinics="clinics"
                :positions="positions"
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
import ChartMixin from './mixins/chart';
import generator from './generator';
import FileSaver from 'file-saver';
import EmployeeRepository from '@/repositories/employee';

export default {
    mixins: [
        ChartMixin,
    ],
    components: {
        ReportFilter,
        DataTable,
    },
    props: {
        elasticSearchClient: Object,
        reportIndex: String,
        clinics: Array,
    },
    data() {
        return {
            employees: [],
        };
    },
    methods: {
        getReportData(updates) {
            this.changeFilters(updates);
            let filters = this.prepareFilters();

            this.fetchData(filters).then(response => {
                this.getEmployees(filters).then(() => {
                    this.tableData = this.prepareDataRows(generator(response));
                    this.loading = false;
                });
            });
        },
        prepareDataRows(data) {
            let rows = [];
            Object.keys(data.byOperator).forEach(key => {
                let operator = data.byOperator[key];

                Object.keys(operator.byMonth).sort().forEach((monthKey, index) => {
                    let month = operator.byMonth[monthKey];
                    rows.push({
                        name: (index === 0) ? this.getName(key) : '',
                        date: this.$formatter.dateFormat(month.date, 'MMMM'),
                        appointments: month.appointments,
                        calls: month.calls,
                        income: month.income,
                        nonProfile: month.nonProfile,
                    });
                });
                rows.push({
                    summary: true,
                    date: __('Всего'),
                    appointments: operator.appointments,
                    calls: operator.calls,
                    income: operator.income,
                    nonProfile: operator.nonProfile,
                });
            });

            let summary = {date: __('Всего'), calls: 0, appointments: 0, income: 0, nonProfile: 0, isTableSummary: true};

            Object.keys(data.byMonth).sort().forEach((key, index) => {
                let month = data.byMonth[key];
                rows.push({
                    name: (index === 0) ? __('Суммарно') : '',
                    date: this.$formatter.dateFormat(month.date, 'MMMM'),
                    appointments: month.appointments,
                    calls: month.calls,
                    income: month.income,
                    nonProfile: month.nonProfile,
                });
                summary.calls += month.calls;
                summary.appointments += month.appointments;
                summary.income += month.income;
                summary.nonProfile += month.nonProfile;
            });

            rows.push(summary);
            return rows;
        },
        getName(id) {
            let employee = this.employees.find(row => row.id == id);
            return employee ? employee.value : null;
        },
        getEmployees(filters = {}) {
            let repo = new EmployeeRepository();
            let filter = {
                employee_clinic: _.onlyFilled({
                    clinic: filters.clinic,
                    position: this.positions.map(pos => pos.id),
                }),
            };
            return repo.fetchList(filter).then((response) => {
                this.employees = response;
                return Promise.resolve();
            });
        },
        exportExcel() {
            this.loading = true;
            let book = generator(this.tableData, 'export');
            book.xlsx.writeBuffer().then((buffer) => {
                FileSaver.saveAs(new Blob([buffer]), __('Звонки/Записи/Приходы.xlsx'))
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
