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
import ReasonRepository from '@/repositories/appointment/status/reason';

export default {
    data() {
        return {
            repository: new ReasonRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    title: __('Название причины выбора статуса'),
                    width: '90%',
                    sortField: 'name',
                    filter: true,
                },
                {
                    name: 'esputnik_no_answer',
                    title: __('Отслеживания недозвонов в eSputnik'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'esputnikNoAnswer',
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
