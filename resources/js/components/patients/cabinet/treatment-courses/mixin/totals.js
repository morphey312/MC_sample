export default {
    computed: {
        totalCost() {
            return this.rows.reduce((sum, val) => {
                return sum + (val.service.cost ? (val.service.quantity * val.service.cost) : 0);
            }, 0);
        },
        totalSelfCost() {
            return this.rows.reduce((sum, val) => {
                return sum + (val.service.self_cost ? (val.service.quantity * val.service.self_cost) : 0);
            }, 0);
        },
        totalAssignedCost() {
            return this.assigned.reduce((sum, val) => {
                return sum + (val.service.cost ? (val.service.quantity * val.service.cost) : 0);
            }, 0);
        },
        totalAssignedSelfCost() {
            return this.assigned.reduce((sum, val) => {
                return sum + (val.service.cost ? (val.service.quantity * val.service.cost) : 0);
            }, 0);
        },
        totalAssignedCount(){
            return this.assigned.length;
        },
        totalCount(){
            return this.rows.length;
        }
    },
}
