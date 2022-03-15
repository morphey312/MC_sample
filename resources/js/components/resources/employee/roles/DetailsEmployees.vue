<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :flex-height="true"
        @header-filter-updated="syncFilters">
    </manage-table>
</template>

<script>
import EmployeeRepository from '@/repositories/employee';
import ClinicRepository from '@/repositories/clinic';
import PositionRepository from '@/repositories/employee/position';

export default {
    props: {
        role: Object,
    },
    data() {
        return {
            repository: new EmployeeRepository(),
            fields: [
                {
                    name: 'full_name',
                    sortField: 'fullName',
                    title: __('ФИО'),
                    width: '30%',
                    filter: true,
                },
                {
                    name: 'clinic_names',
                    title: __('Клиника'),
                    width: '25%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new ClinicRepository(),
                    filterField: 'clinic',
                },
                {
                    name: 'position_names',
                    title: __('Должность'),
                    width: '25%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new PositionRepository(),
                    filterField: 'position',
                },
                {
                    name: 'status_names',
                    title: __('Статус'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.fromHandbook('employee_status', value);
                    },
                    filter: 'employee_status',
                    filterField: 'status',
                },
            ],
            filters: {
                role: this.role.id,
            },
        };
    },
    methods: {
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
}
</script>