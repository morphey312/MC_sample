<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
    </manage-table>
</template>

<script>
import CashTransferRepository from '@/repositories/employee/cash-transfer';
import DateRangeHeaderFilter from '@/components/general/table/DateRangeHeaderFilter.vue';
export default {
    data() {
        return {
            repository: new CashTransferRepository(),
            fields: [
                {
                    name: 'date',
                    filterField: 'date_range',
                    title: __('Дата изменения'),
                    width: '5%',
                    sortField: 'created',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateRangeHeaderFilter,
                },
                {
                    name: 'cashier.full_name',
                    title: __('Кто произвел выемку'),
                    width: '15%',
                },
                {
                    name: 'amount',
                    title: __('Cумма'),
                    dataClass: 'text-right',
                    width: '5%',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
            ],
            filters: {},
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            scopes: [
                'cashier',
                'source',
                'destination',
            ],
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
}
</script>
