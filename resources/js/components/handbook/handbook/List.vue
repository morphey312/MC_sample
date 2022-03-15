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
        <template slot="spacer">
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import HandbookRepository from '@/repositories/handbook';

export default {
    props: {
        header: {
            type: String,
            default: __('Значение'),
        },
        category: String,
        modelKey: Boolean,
    },
    data() {
        return {
            repository: new HandbookRepository(this.category),
            model: null,
            fields: [
                {
                    name: 'value_i18n',
                    sortField: 'value',
                    title: this.header,
                    width: '25%',
                    filter: true,
                },
                {
                    name: 'spacer',
                    title: '',
                    width: '75%',
                    dataClass: 'no-dash',
                },
            ],
            initialSortOrder: [
                {field: 'value', direction: 'asc'},
            ],
            filters: {
            },
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
};
</script>