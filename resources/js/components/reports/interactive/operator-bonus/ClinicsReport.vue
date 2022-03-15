<template>
    <div v-loading="loading">
        <section class="grey">
            <clinic-filter
                ref="filter"
                :initial-state="filters"
                :clinics="clinics"
                :positions="positions"
                @changed="getReportData"
            />
        </section>
        <section
            v-if="tableData.length != 0"
            class="pt-0"
        >
            <clinic-table
                :table-data="tableData"
                :operator-names="operatorNames"
            />
        </section>
    </div>
</template>
<script>
import ReportMixin from './mixins/report';
import ClinicFilter from './ClinicFilter.vue';
import ClinicTable from './ClinicTable.vue';
import generator from './generator';

export default {
    components: {
        ClinicFilter,
        ClinicTable,
    },
    mixins: [
        ReportMixin,
    ],
    data() {
        return {
            operatorNames: {},
            isOperator: false,
        }
    },
    methods: {
        getReportData(updates) {
            this.changeFilters(updates);
            if (_.isVoid(this.filters.clinic)) {
                return this.$error(__('Выберите клинику'))
            }
            this.fetchBonusData(this.filters).then(response => {
                this.fetchProcessLogData(this.filters).then(processLogs => {
                    let totals = this.getTotals(response);
                    let operatorLogs = this.getOperators(processLogs);
                    let data = generator({
                        totals,
                        clinics: this.clinicBonuses,
                        operators: operatorLogs,
                    });
                    if (!_.isEmpty(data[this.filters.clinic])) {
                        this.setOperatorNames(data[this.filters.clinic].operatorKeys);
                        if(!_.isEmpty(this.operatorNames)) {
                            this.tableData = data[this.filters.clinic].results;
                        } else {
                            this.$info(__('Нет данных для отображения'));
                        }
                    } else {
                        this.$info(__('Нет данных для отображения'));
                    }
                    this.loading = false;
                });
            });
        },
        checkIsOperator(clinic) {
            return this.$store.state.user.employee.clinics.some(c => c.id === clinic && c.is_operator);
        },
        setOperatorNames(keys) {
            this.operatorNames = {};
            if(this.checkIsOperator(this.filters.clinic)) {
                let operator = this.operators.find(item => item.id == this.$store.state.user.employee.id);
                if(operator) {
                    this.operatorNames[this.$store.state.user.employee.id] = operator.full_name;
                }

                return;
            }
            keys.forEach(id => {
                let operator = this.operators.find(item => item.id == id);
                if (operator) {
                    this.operatorNames[id] = operator.full_name;
                }
            });
        },
        getTotals(data) {
            let clinicId = this.filters.clinic;
            return this.getOperatorTotals(data, clinicId);
        },
    }
}
</script>
