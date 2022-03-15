<template>
    <manage-table 
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :selectable-rows="true"
        :flex-height="true"
        @loaded="loaded"
        @selection-changed="selectionChanged"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
        <template
            slot="icon"
            slot-scope="props" >
            <list-icon :model="props.rowData" />
        </template>
    </manage-table>
</template>

<script>
import IconRepository from '@/repositories/discount-card-type/icon';
import ListIcon from './ListIcon.vue';

export default {
    components: {
        ListIcon,
    },
    data() {
        return {
            repository: new IconRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '50%',
                    filter: true,
                },
                {
                    name: 'icon',
                    title: __('Иконка'),
                    width: '50%',
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