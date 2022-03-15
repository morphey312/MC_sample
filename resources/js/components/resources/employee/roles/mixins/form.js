export default {
    data() {
        return {
            activeTab: 'general',
        };
    },
    created() {
        this.errorHandler = (errors) => {
            if (('name' in errors) || ('permissions' in errors)) {
                this.activeTab = 'general';
            } else if ('users' in errors) {
                this.activeTab = 'users';
            }
        }
    },
    beforeMount() {
        this.$eventHub.$on('validationErrors', this.errorHandler);
    },
    beforeDestroy() {
        this.$eventHub.$off('validationErrors', this.errorHandler);
    },
    methods: {
        changeTab(tab) {
            this.activeTab = tab;
        },
    }
};