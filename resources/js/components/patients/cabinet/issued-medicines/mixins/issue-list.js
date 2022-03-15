import IssuedMedicineRepository from '@/repositories/patient/issued-medicine';
import AssignedMedicineRepository from '@/repositories/patient/assigned-medicine';

export default {
    props: {
        patient: Object,
    },
    data() {
        return {
            repository: new IssuedMedicineRepository(),
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
            scopes: [
                'issuer',
                'assigned_medicine',
            ],
            issuedCost: 0,
            issuedQuantity: 0,
            toIssueQuantity: 0,
        }
    },
    methods: {
        loaded() {
            this.getToIssueQuantity();
            let rows = this.getTableRows();
            this.issuedCost = this.getIssuedCost(rows);
            this.issuedQuantity = this.getIssuedQuantity(rows);
        },
        getIssuedQuantity(rows) {
            return rows.reduce((sum, row) => {
                return sum += Number(row.issued);
            }, 0);
        },
        getToIssueQuantity() {
            let repo = new AssignedMedicineRepository();
            let filters = this.getToIssueFilters();

            repo.fetch(filters, null, ['assigned_medicine']).then((response) => {
                if (response.rows && response.rows.length > 0) {
                    this.toIssueQuantity =  response.rows.reduce((sum, row) => {
                        return sum += Number(row.quantity) - Number(row.issued);
                    }, 0);
                    return;
                }
                this.toIssueQuantity = 0;
            });
        },
        getTableRows() {
            return this.getTable().getData();
        },
        getTable() {
            return this.$refs.table;
        },
        syncFilters(updates) {
            this.filters = {...this.filters, ...updates};
        },
        getToIssue(row) {
            return Number((row.medicine.quantity - row.medicine.issued_quantity).toFixed(3));
        },
        refresh() {
            this.getTable().refresh();
        },
    }
}
