<template>
    <sms-list
        ref="table"
        :filters="filters"
        @header-filter-updated="filtersUpdated"
        @selection-changed="setActiveItem"
        @loaded="refreshed">
    </sms-list>
</template>
<script>
import SmsList from './SMSList.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        SmsList,
    },
    props: {
        tableFilters: Object,
    },
    data() {
        return {
            filters: this.tableFilters,
        };
    },
    methods: {
        filtersUpdated(updates) {
            this.$emit('header-filter-updated', updates);
        },
    },
    watch: {
        tableFilters(val) {
            this.filters = val;
        },
    },
}
</script>
