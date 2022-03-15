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
import LeagalEntityRepository from '@/repositories/legal-entity';
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new LeagalEntityRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Полное название'),
                    width: '25%',
                    filter: true,
                },
                {
                    name: 'short_name',
                    sortField: 'short_name',
                    filterField: 'short_name',
                    filter: true,
                    title: __('Краткое название'),
                    width: '25%',
                },
                {
                    name: 'post_address',
                    sortField: 'post_address',
                    filterField: 'post_address',
                    filter: true,
                    title: __('Почтовый адрес'),
                    width: '25%',
                },
                {
                    name: 'clinic_names',
                    title: __('Клиники'),
                    width: '25%',
                    filter: new ClinicRepository(),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filterProps: {
                        multiple: true,
                    },
                    filterField: 'clinic',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
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
            this.$emit('header-filter-updated', updates);
        },
    }
}
</script>
