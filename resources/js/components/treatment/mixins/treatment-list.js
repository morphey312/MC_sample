export default {
	methods: {
        sequenceNumber(rowIndex) {
            if(this.$refs.table.$refs.pagination){
                let data = this.$refs.table.$refs.pagination._data;

                if(data.currentPage == 1) {
                    return (1 + rowIndex);    
                }
                
                return (1 + rowIndex) + (data.currentPage - 1)* data.pageSize;    
            }
        },
    },
}