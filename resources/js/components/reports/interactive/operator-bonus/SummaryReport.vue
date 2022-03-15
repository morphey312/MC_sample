<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="clinics"
                        :clearable="true"
                        :multiple="true"
                        :filterable="true"
                        property="clinic"
                        :label="__('Клиника')"
                    />
                </el-col>
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="positions"
                        :multiple="true"
                        :clearable="true"
                        property="position"
                        :label="__('Должность')"
                    />
                </el-col>
                <el-col :span="6">
                    <form-row
                        name="dates"
                        :label="__('Период')"
                    >
                        <div class="form-input-group">
                            <form-date
                                :entity="filters"
                                property="date_start"
                                :clearable="true"
                            />
                            <form-date
                                :entity="filters"
                                property="date_end"
                                :clearable="true"
                            />
                        </div>
                    </form-row>
                </el-col>
            </el-row>
            <div class="buttons">
                <el-button
                    type="primary"
                    @click="getReportData()"
                >
                    {{ __('Показать') }}
                </el-button>
            </div>
        </section>
        <section
            v-if="tableData.length != 0"
            class="pt-0"
        >
            <summary-table
                :table-data="tableData"
                :clinics="reportClinics"
                @export="exportExcel"
            />
        </section>
    </div>
</template>
<script>
import SummaryTable from './SummaryTable.vue';
import ReportMixin from './mixins/report';
import generator from './generator';
import fileGenerator from '@/components/reports/generators/call-center/operator-bonuses';
import FileSaver from 'file-saver';

export default {
    components: {
        SummaryTable,
    },
    mixins: [
        ReportMixin,
    ],
    data() {
        return {
            filters: {
                clinic: [],
                position: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
            },
            reportClinics: [],
            totals: [],
            processLogs: [],
            operatorLogs: [],
            isOperator: false,
        }
    },
    methods: {
        getReportData() {
            let filters = this.prepareFilters();
            this.fetchBonusData(filters).then(data => {
                this.fetchProcessLogData(this.filters).then(processLogs => {
                    this.processLogs = processLogs;
                    this.prepareTableData(data, filters);
                    this.loading = false;
                });
            });
        },
        prepareTableData(data, filters) {
            this.totals = this.getTotals(data, filters);
            this.operatorLogs = this.getOperators(this.processLogs);
            this.isOperator = this.checkIsOperator();
            if(this.isOperator) {
                this.operatorLogs = this.operatorLogs.filter(operator => operator.id == this.$store.state.user.employee.id);
            }
            data = null;
            this.getReportClinics(filters);
            this.tableData = generator({
                totals: this.totals,
                clinics: this.clinicBonuses,
                operators: this.operatorLogs,
                positions: this.positions,
            }, 'summary');
        },
        checkIsOperator() {
            return this.$store.state.user.employee.clinics.find(c => c.status === 'working').is_operator;
        },
        getReportClinics(filters) {
            this.reportClinics = [];
            let clinicList = [];

            filters.clinic.forEach(id => {
                let clinic = this.clinics.find(clinic => clinic.id == id);
                if (clinic) {
                    clinicList.push({id, name: clinic.value});
                }
            });
            this.reportClinics = _.sortBy(clinicList, 'name');
        },
        getTotals(data, filters) {
            let clinics = filters.clinic;
            let totals = [];
            clinics.forEach(clinicId => {
                totals = [...totals, ...this.getOperatorTotals(data, clinicId, false)];
            });
            return totals;
        },
        exportExcel() {
            let data = {
                totals: this.totals,
                clinics: this.clinicBonuses,
                operators: this.operatorLogs,
                positions: this.positions,
            };
            fileGenerator(data).then((book) => {
                book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), __('Отчет_Бонусы_операторов.xlsx'))
                }).catch((err) => {
                    console.error(err);
                    this.$error(__('Не удалось сохранить отчет'));
                });
            });
        },
    },
}
</script>
