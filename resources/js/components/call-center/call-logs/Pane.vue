<template>
    <div class="call-logs-pane">
        <section class="grey filter">
            <call-logs-filter 
                ref="filter"
                :start-collapsed="!displayFilter"
                :initial-state="filters"
                @changed="changeFilters"
                @cleared="clearFilters" />
        </section>
        <section class="grey-cap">
            <call-logs-list 
                :filters="filters"
                @header-filter-updated="syncFilters" />
        </section>
    </div>
</template>

<script>
import CallLogsFilter from './Filter.vue';
import CallLogsList from './List.vue';
import TablesMixin from '../mixins/tables';
import {PatientContact} from '@/services/sip-ua/process-state';

export default {
    mixins: [
        TablesMixin,
    ],
    components: {
        CallLogsFilter,
        CallLogsList,
    },
    mounted() {
        this.watchViopContactChange();
        this.watchClearVoipData();
    },
    methods: {
        getInitialFilters(defaultFilters) {
            let contact = this.getVoipContact();
            return contact 
                ? this.filterByContact(contact) 
                : this.getStoredFilter(defaultFilters);
        },
        getFilterUid() {
            return 'call-center-call-log';
        },
        getDefaultFilters() {
            return {
                date_from: this.$moment().format('YYYY-MM-DD'),
                date_to: this.$moment().format('YYYY-MM-DD'),
            };
        },
    },
}
</script>