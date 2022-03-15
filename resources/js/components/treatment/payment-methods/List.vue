<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import PaymentMethodRepository from '@/repositories/payment-method';

export default {
    data() {
        return {
            repository: new PaymentMethodRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    title: __('Название'),
                    width: '30%',
                    filter: true,
                },
                {
                    name: 'clinic_names',
                    title: __('Клиники'),
                    width: '70%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return value.join(', ');
                    }
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {},
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