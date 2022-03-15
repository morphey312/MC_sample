export default {
    props: {
        initialState: {
            type: Object,
            default: () => ({}),
        }
    },
    data() {
        return {
            filter: {},
        }
    },
    beforeMount() {
        this.initFilter(this.initialState);
    },
    methods: {
        changed(filters) {
            this.$emit('changed', this.makeFilters(filters));
        },
        cleared() {
            this.$emit('cleared');
        },
        sync(updates) {
            this.filter = {...this.filter, ...updates};
            this.changed(this.filter);
        },
        initFilter(fromState = {}) {
            this.filter = {...fromState};
        },
        makeFilters(filters) {
            return _.onlyFilled(filters);
        },
    },
    watch: {
        initialState(value) {
            this.initFilter(value);
        },
    },
};