import lts from '@/services/lts';

export default {
    methods: {
        getFilterUid() {
            return this.$router.currentRoute.name;
        },
        getStoredFilters() {
            return lts.storedFilters || {};
        },
        getStoredFilter(otherwise = {}) {
            let uid = this.getFilterUid();
            if (uid !== false) {
                let filters = this.getStoredFilters();
                return filters[uid] || otherwise;
            }
            return otherwise;
        },
        hasStoredFilter() {
            return !_.isEmpty(this.getStoredFilter());
        },
        storeFilter(filters) {
            let uid = this.getFilterUid();
            if (uid !== false) {
                let stored = this.getStoredFilters();
                stored[uid] = filters;
                lts.storedFilters = stored;
            }
        },
        forgetFilter() {
            let stored = this.getStoredFilters();
            let uid = this.getFilterUid();
            delete stored[uid];
            lts.storedFilters = stored;
        },
    },
}