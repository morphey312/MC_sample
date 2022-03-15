<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        table-height="auto"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import DepartmentRepository from '@/repositories/department';
import ClinicRepository from '@/repositories/clinic';

export default {
    data() {
        return {
            repository: new DepartmentRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '50%',
                    filter: true,
                },
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    width: '25%',
                    dataClass: 'no-dash',
                    filterField: 'clinic',
                    filter: new ClinicRepository(),
                },
                {
                    name: 'type',
                    title: __('Тип'),
                    filter: 'department_type',
                    filterField: 'type',
                    sortField: 'type',
                    width: '25%',
                    formatter: (value) => {
                        return this.$handbook.getOption('department_type', value);
                    },
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {},
            scopes: [
                'clinic'
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
