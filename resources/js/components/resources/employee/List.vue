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
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
        <template
            slot="full_name"
            slot-scope="props" >
            <div class="has-icon">
                <span class="ellipsis">
                    {{ props.rowData.full_name }}
                </span>
                <svg-icon
                    v-if="props.rowData.employee_clinics.length > 1"
                    name="info-alt"
                    class="icon-tiny icon-grey"
                    @click.stop="showDetails(props.rowData)" />
            </div>
        </template>
    </manage-table>
</template>

<script>
import EmployeeRepository from '@/repositories/employee';
import ClinicRepository from '@/repositories/clinic';
import PositionRepository from '@/repositories/employee/position';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeDetails from './Details.vue';
import RoleRepository from "@/repositories/role";

export default {
    props: {
        filters: Object,
    },
    data() {
        let specializationRepo = new SpecializationRepository();

        return {
            repository: new EmployeeRepository(),
            fields: [
                {
                    name: 'full_name',
                    sortField: 'fullName',
                    title: __('ФИО'),
                    width: '17%',
                    filter: true,
                },
                {
                    name: 'clinic_names',
                    title: __('Клиника'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('employees'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['clinics.clinic'],
                },
                {
                    name: 'position_names',
                    title: __('Должность'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new PositionRepository(),
                    filterField: 'position',
                    scopes: ['clinics.position'],
                },
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: specializationRepo,
                    filterField: 'specialization',
                    scopes: ['clinics.specializations'],
                },
                {
                    name: 'phone',
                    title: __('Телефон'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'user.role_names',
                    title: __('Группы доступа'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new RoleRepository(),
                    filterField: 'role',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['user.roles'],
                },
                {
                    name: 'ehealth_request_status',
                    title: __('Запрос eHealth'),
                    width: '8%',
                    formatter: (value) => {
                        return this.$handbook.getOption('ehealth_request_status', value);
                    },
                    filter: 'ehealth_request_status',
                },
            ],
            specializationRepo,
            initialSortOrder: [
                {field: 'fullName', direction: 'asc'},
            ],
            scopes: [
                'default',
                'permissions',
                'clinics.doctor',
                'clinics.clinic',
            ]
        };
    },
    watch: {
        ['filters.clinic']: {
            immediate: true,
            handler() {
                this.specializationRepo.setFilters(this.getSpecializationFilters());
            }
        },
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
        showDetails(employee) {
            this.$modalComponent(EmployeeDetails, {
                employee,
            }, {}, {
                header: employee.full_name,
                width: '700px',
                customClass: 'no-footer',
            });
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filters.clinic,
            });
        },
    }
}
</script>
