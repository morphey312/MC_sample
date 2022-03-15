<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
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
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    data() {
        return {
            repository: new EmployeeRepository({
                filters: {
                    position_type: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR,
                }
            }),
            fields: [
                {
                    name: 'full_name',
                    sortField: 'full_name',
                    title: __('Оператор'),
                    width: '40%',
                    filter: true,
                },
                {
                    name: 'operator_bonus.evaluation',
                    title: __('Оценочный лист'),
                    width: '30%',
                },
                {
                    name: 'operator_bonus.clinic_names',
                    title: __('Клиники'),
                    width: '30%',
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    },
                },
            ],
            initialSortOrder: [
                {field: 'full_name', direction: 'asc'},
            ],
            filters: {},
            scopes: ['operator_bonus'],
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