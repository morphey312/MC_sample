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
import DaySheetTimeBlockReasonRepository from '@/repositories/day-sheet/time-block-reason';

export default {
    data() {
        return {
            repository: new DaySheetTimeBlockReasonRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    title: __('Название'),
                    width: '30%',
                    filter: true,
                },
            ],
            initialSortOrder: [
                {field: 'name_i18n', direction: 'asc'},
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
