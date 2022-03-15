export default {
    computed: {
        totalSelected() {
            return this.selectedRows.length;
        },
        emptySelected() {
            return this.totalSelected == 0;
        },
        totalCost() {
            let total = 0;

            if (this.totalSelected !== 0){
                this.selectedRows.forEach(row => {
                    if (!isNaN(row.cost)) {
                        total = total + Number(row.cost)
                    }
                });
            }
            return this.$formatter.numberFormat(total);
        },
        totalSelfCost() {
            let total = 0;
            if (this.totalSelected !== 0){
                this.selectedRows.forEach(row => {
                    if (!isNaN(row.self_cost)) {
                        total = total + Number(row.self_cost)
                    }
                });
            }
            return this.$formatter.numberFormat(total);
        },
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        confirm() {
            this.loading = true
            this.$emit('selected', this.selectedRows);
        },
    }
}
