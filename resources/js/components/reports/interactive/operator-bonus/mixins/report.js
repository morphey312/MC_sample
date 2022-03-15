import ManageMixin from '@/mixins/manage';
import ChartFormatMixin from '@/components/reports/interactive/mixins/chart-format';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ManageMixin,
        ChartFormatMixin,
    ],
    props: {
        elasticSearchClient: Object,
        bonusIndex: String,
        wrapupIndex: String,
        clinics: Array,
        positions: Array,
        operators: Array,
        clinicBonuses: Array,
    },
    data() {
        return {
            tableData: [],
        }
    },
    methods: {
        getFilterUid() {
            return false;
        },
        prepareFilters() {
            let filters = {...this.filters};
            
            if (!this.filters.clinic || this.filters.clinic.length == 0) {
                filters.clinic = this.clinics.map(c => c.id);
            }
            return filters;
        },
        getBaseFilters(filters) {
            return [
                {
                    range: {
                        date: {
                            gte: filters.date_start,
                            lte: filters.date_end
                        }
                    }
                },
            ];
        },
        fetchBonusData(filters = []) {
            this.loading = true;
            return this.elasticSearchClient.getHits(this.bonusIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                query: {
                    bool: {
                        filter: [
                            ...this.getBaseFilters(filters),
                            ...(filters.position && filters.position.length != 0 ? [
                                {
                                    terms: {
                                        position_id: filters.position
                                    }
                                }
                            ] : [])
                        ],
                    }
                }
            }, true)
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
        fetchProcessLogData(filters = []) {
            this.loading = true;
            return this.elasticSearchClient.getHits(this.wrapupIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                query: {
                    bool: {
                        filter: [...this.getBaseFilters(filters)],
                    }
                }
            }, true)
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
        getOperators(processLogs) {
            return this.operators.map(operator => {
                let operatorLogs = processLogs.filter(log => log.operator_id == operator.id);
                let totalDuration = operatorLogs.reduce((total, row) => {
                    return total + row.duration;
                }, 0);
                let wrapups = operatorLogs.reduce((total, row) => {
                    return total + row.wrapup_count;
                }, 0);
                operator.wrapups_average = totalDuration / wrapups;
                return operator;
            });
        },
        getOperatorTotals(data, clinicId, clinicSeparate = true) {
            let clinic = data.filter(item => item.clinic_id == clinicId);
            let operators = [];
            let operatorGroups = _.groupBy(clinic, 'operator_id');

            Object.keys(operatorGroups).forEach(operatorId => {
                let operator = this.operators.find(o => o.id == operatorId);
                let employeeClinic = operator ? operator.employee_clinics.find(ec => ec.clinic_id == clinicId) : null;
                if (!employeeClinic) {
                    return;
                }
                if (this.inactiveStatus(employeeClinic.status)) {
                    return;
                }
                
                let rows = operatorGroups[operatorId];
                let defaultRow = {
                    clinic_id: clinicId,
                    for_appointments: 0,
                    for_calls: 0,
                    for_incomes: 0,
                    for_repeated: 0,
                    operator_id: operatorId,
                    total_appointments: 0,
                    total_calls: 0,
                    total_incomes: 0,
                };
                
                operators.push({
                    ...defaultRow,
                    for_appointments: 1,
                    total_appointments: rows.filter(row => row.for_appointments == 1).length,
                });
                operators.push({
                    ...defaultRow,
                    for_calls: 1,
                    total_calls: rows.filter(row => row.for_calls == 1).length,
                });
                operators.push({
                    ...defaultRow,
                    for_incomes: 1,
                    total_incomes: rows.filter(row => row.for_incomes == 1).length,
                });
                operators.push({
                    ...defaultRow,
                    for_repeated: 1,
                    total_appointments: clinicSeparate ? 
                        data.filter(row => row.for_repeated == 1 && row.operator_id == operatorId).length //count all clinics repeated appointments for operator
                        : rows.filter(row => row.for_repeated == 1).length, 
                });
            });
            return operators;
        },
        inactiveStatus(status) {
            return [
                CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING, 
                CONSTANTS.EMPLOYEE.STATUSES.REMOVED
            ].indexOf(status) !== -1;
        }
    }
}