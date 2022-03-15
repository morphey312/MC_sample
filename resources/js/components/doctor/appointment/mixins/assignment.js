export default {
    methods: {
        findAssignment(type) {
            return this.cardAssigments.find(item => {
                if (_.isFilled(item.recordable)) {
                    return item.recordable.type === type;
                }
            });
        },
    },
}