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
import AppointmentRepository from '@/repositories/appointment';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import SpecializationRepository from '@/repositories/specialization';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        let specializationRepo = new SpecializationRepository();
        let employeeRepo = new EmployeeRepository({filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR}, limit: 50});

        return {
            repository: new AppointmentRepository(),
            fields: [
                {
                    name: 'clinic_name',
                    sortField: 'clinicName',
                    title: __('Клиника'),
                    width: '150px',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('appointments'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'date',
                    sortField: 'date',
                    title: __('Дата записи'),
                    width: '110px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                    filterField: 'date',
                },
                {
                    name: 'specialization_name',
                    sortField: 'specialization_name',
                    title: __('С-ция записи'),
                    width: '120px',
                    filter: specializationRepo,
                    filterField: 'specialization',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['specialization'],
                },
                {
                    name: 'card_only_number',
                    filterField: 'patient_card_number',
                    title: __('№ карты'),
                    width: '90px',
                    dataClass: 'no-dash',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                    scopes: ['card_number'],
                },
                {
                    name: 'patient',
                    sortField: 'patient_name',
                    title: __('Пациент'),
                    width: '200px',
                    formatter: (value) => {
                        return value.full_name;
                    },
                    filter: true,
                    filterField: 'patient_name',
                },
                {
                    name: 'doctor_name',
                    sortField: 'doctor_name',
                    title: __('Врач'),
                    filter: employeeRepo,
                    filterField: 'doctor',
                    filterProps: {
                        multiple: true,
                        limit: 70
                    },
                    width: '200px',
                },
                {
                    name: 'rejection_reason',
                    title: __('Причина отказа'),
                    width: '200px',
                    dataClass: 'no-ellipsis',
                    formatter: (value) => {
                        return this.$handbook.getOption('reason_refusing_treatment', value);
                    },
                    filter: 'reason_refusing_treatment',
                    filterField: 'rejection_reason',
                    filterProps: {
                        multiple: true,
                    },
                },
            ],
            employeeRepo,
            specializationRepo,
            initialSortOrder: [
                {field: 'created', direction: 'asc'},
            ],
            scopes: [
                'clinic',
                'doctor',
                'patient',
            ],
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
            this.$emit('header-filter-updated', updates);
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filters.clinic,
                status: 1,
            });
        },
        getDoctorFilters() {
            return _.onlyFilled({
                clinic: this.filters.clinic,
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                status: CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                employment_range: {
                    date_start_working: this.filters.date_start,
                    date_employment_end: this.filters.date_end,
                    clinics: [this.filters.clinic]
                },
            });
        },
    },
    watch: {
        ['filters.clinic']: {
            immediate: true,
            handler() {
                this.specializationRepo.setFilters(this.getSpecializationFilters());
                this.employeeRepo.setFilters(this.getDoctorFilters());
            }
        },
    },
}
</script>