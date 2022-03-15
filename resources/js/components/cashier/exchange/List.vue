<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        @header-filter-updated="syncFilters">
    </manage-table>
</template>
<script>
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import ExchangeRateRepository from '@/repositories/exchange-rate';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new ExchangeRateRepository(),
            fields: [
                {
                    name: 'date',
                    title: __('Дата'),
                    width: '150px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    sortField: 'date',
                    filterField: 'date',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'value',
                    title: __('Курс'),
                    width: '150px',
                },
                {
                    name: 'code',
                    title: __('Валюта'),
                    width: '150px',
                    formatter: (val) => {
                        return this.$handbook.getOption('currency', val);
                    },
                },
            ],
            initialSortOrder: [
                {field: 'date', direction: 'desc'},
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
    },
}
</script>