<template>
    <div class="missed-calls-pane">
        <section class="grey filter">
            <sms-reminders-filer
                ref="filter"
                :start-collapsed="!displayFilter"
                :initial-state="filters"
                @changed="changeFilters"
                @cleared="clearFilters" />
        </section>
        <section class="grey-cap">
            <sms-reminders-list
                :filters="filters"
                @header-filter-updated="syncFilters" />
        </section>
    </div>
</template>

<script>
import SmsRemindersFiler from './Filter.vue';
import SmsRemindersList from './List.vue';
import TablesMixin from '../mixins/tables';

export default {
    mixins: [
        TablesMixin,
    ],
    components: {
        SmsRemindersFiler,
        SmsRemindersList,
    },
    methods: {
        getFilterUid() {
            return 'sms-reminders';
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
