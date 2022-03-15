<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :scopes="scopes"
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
import AnalysesCandidateRepository from '@/repositories/analysis/candidate';

export default {
    props: {
        filters: Object
    },
    data() {
        return {
            repository: new AnalysesCandidateRepository(),
            fields: [
                {
                    name: 'code',
                    title: __('Код анализа Lab'),
                    sortField: 'code',
                    width: '100px',
                    filter: true,
                },
                {
                    name: 'name',
                    title: __('Название анализа из MC+LAB'),
                    filterField: 'name',
                    filter: true,

                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            scopes: ['analysis'],
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
            this.$emit('header-filter-updated', updates);
        },
    },
}
</script>
