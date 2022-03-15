import CONSTANTS from '@/constants';

export default {
    data() {
        return {
            totalAmount: 0,
        }
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        loaded() {
            this.setTotal();
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        getTable() {
            return this.$refs.table;
        },
        getTableRows() {
            return this.getTable().getData();
        },
        unselectAll(){
            this.getTable().unselectAll();
        },
        setTotal() {
            let rows = this.getTableRows();
            let amount = 0;
            if (rows.length !== 0) {
                amount = rows.reduce((total, row) => {
                    if (row.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
                        return total -= Number(row.payed_amount);
                    }
                    return total += Number(row.payed_amount);
                }, 0);
            }
            this.totalAmount = amount;
        },
        refresh() {
            let table = this.getTable();
            if (table) {
                table.refresh();
            } 
        },
    }
}