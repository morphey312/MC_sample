<template>
    <div class="call-process-logs-pane">
        <section class="grey filter">
            <call-process-logs-filter
                ref="filter"
                :start-collapsed="!displayFilter"
                :initial-state="filters"
                @changed="changeFilters"
                @cleared="clearFilters" />
        </section>
        <section class="grey-cap">
            <call-process-logs-list 
                :filters="filters"
                @header-filter-updated="syncFilters" />
        </section>
    </div>
</template>

<script>
import CallProcessLogsFilter from './Filter.vue';
import CallProcessLogsList from './List.vue';
import TablesMixin from '../mixins/tables';

export default {
    mixins: [
        TablesMixin,
    ],
    components: {
        CallProcessLogsFilter,
        CallProcessLogsList,
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
            return 'call-center-process-log';
        },
        getDefaultFilters() {
            let user = this.getUser();
            
            return {
                ...(user.isOperator ? {operator: user.employee_id} : {}),
                date_from: {date: this.$moment().format('YYYY-MM-DD'), time: null},
                date_to: {date: this.$moment().format('YYYY-MM-DD'), time: null},
            };
        },
    },
}
</script>