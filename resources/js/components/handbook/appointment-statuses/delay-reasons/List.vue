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
import DelayReasonRepository from '@/repositories/appointment/status/delay-reason';

export default {
    data() {
        return {
            repository: new DelayReasonRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    title: __('Название причины задержки изменения статуса'),
                    width: '100%',
                    sortField: 'name',
                    filter: true,
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'}
            ],
            filters: {},
        }
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
