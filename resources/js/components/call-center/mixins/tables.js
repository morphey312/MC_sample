import FilterState from '@/mixins/filter-state';
import {PatientContact} from '@/services/sip-ua/process-state';

export default {
    mixins: [
        FilterState,
    ],
    data() {
        let defaultFilters = this.getDefaultFilters();
        let filters = this.getInitialFilters(defaultFilters);

        return {
            filters,
            displayFilter: !_.isEqual(filters, defaultFilters),
        };
    },
    created() {
        this.onVoipDataCleared = () => {
            let defaultFilters = this.getDefaultFilters();
            if (!_.isEqual(this.filters, defaultFilters)) {
                this.clearFilters();
            }
        }
    },
    beforeDestroy() {
        this.$eventHub.$off('voip:cleardata', this.onVoipDataCleared);
    },
    methods: {
        getInitialFilters(defaultFilters) {
            return this.getStoredFilter(defaultFilters);
        },
        changeFilters(filters) {
            this.storeFilter(filters);
            this.filters = filters;
        },
        clearFilters() {
            this.forgetFilter();
            this.filters = this.getDefaultFilters();
        },
        syncFilters(updates) {
            this.$refs.filter.sync(updates);
        },
        getDefaultFilters() {
            return {};
        },
        getUser() {
            return this.$store.state.user;
        },
        getVoipContact() {
            return this.$store.state.processState.currentContact;
        },
        filterByContact(contact) {
            return (contact instanceof PatientContact) ? {
                patient: contact.id,
            } : {
                phone_number: `=${contact.defaultNumber}`,
            };
        },
        watchViopContactChange() {
            this.$watch('$store.state.processState.currentContact', (contact) => {
                if (contact) {
                    this.changeFilters(this.filterByContact(contact));
                }
            });
        },
        watchClearVoipData() {
            this.$eventHub.$on('voip:cleardata', this.onVoipDataCleared);
        },
    },
};