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
import CountryRepository from '@/repositories/country';

export default {
    data() {
        return {
            repository: new CountryRepository(),
            fields: [
                {
                    name: 'name',
                    title: __('Название'),
                    width: '30%',
                    filter: true,
                },
                {
                    name: 'code',
                    title: __('Код ISO'),
                    width: '10%',
                    filter: true,
                },
                {
                    name: 'spacer',
                    title: '',
                    width: '60%',
                    dataClass: 'no-dash',
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
