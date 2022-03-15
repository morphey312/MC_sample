<template>
    <div class="missed-calls-pane">
        <section class="grey filter">
            <missed-calls-filter 
                ref="filter"
                :start-collapsed="!displayFilter"
                :initial-state="filters"
                @changed="changeFilters"
                @cleared="clearFilters" />
        </section>
        <section class="grey-cap">
            <missed-calls-list 
                :filters="filters"
                @header-filter-updated="syncFilters" />
        </section>
    </div>
</template>

<script>
import MissedCallsFilter from './Filter.vue';
import MissedCallsList from './List.vue';
import TablesMixin from '../mixins/tables';

export default {
    mixins: [
        TablesMixin,
    ],
    components: {
        MissedCallsFilter,
        MissedCallsList,
    },
    methods: {
        getFilterUid() {
            return 'call-center-missed-calls';
        },
        getDefaultFilters() {
            return {
                date_from: this.$moment().format('YYYY-MM-DD'),
                date_to: this.$moment().format('YYYY-MM-DD'),
                missed: 24,
            };
        },
    },
}
</script>