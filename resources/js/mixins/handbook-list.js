export default {
    data() {
        return {
            selectedRow: {}
        }
    },
    mounted(){
        this.$watch('filters', (val) => {
            this.selectedRow = {};
            this.showSelectedRow(null);
        });
    },
    methods: {
        addRowClass(dataItem, index) {
            let classList = '';

            if(this.selectedRow && (this.selectedRow.id == dataItem.id)){
                classList += ' selected-table-row ';
            }

            return classList;
        },
        showSelectedRow(item) {
            if(item && item.data){
                this.selectedRow = item.data;
            }
            this.$emit('select-row', this.selectedRow);
        },
        refresh(dissmiss = false) {
            this.$refs.table.refresh();

            if(dissmiss){
                this.selectedRow = {};
            }

            this.showSelectedRow('select-row', this.selectedRow);
        },
        countTableData() {
            if(this.$refs.table.getRowsCount() === 0){
                this.showSelectedRow(null);
            }
        },
    }
}