<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :scopes="scopes"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import PatientRepository from '@/repositories/patient';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import DateRangeHeaderFilter from '@/components/general/table/DateRangeHeaderFilter.vue';
import InformationSourceRepository from '@/repositories/patient/information-source';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new PatientRepository({
                limitClinics: this.$isAccessLimited('patients')
            }),
            fields: [
                {
                    name: 'full_name',
                    sortField: 'full_name',
                    title: __('ФИО'),
                    width: '15%',
                    dataClass: 'no-ellipsis',
                    filter: true,
                },
                {
                    name: 'contact_details',
                    sortField: 'phone_number',
                    filterField: 'phone',
                    title: __('Телефон'),
                    width: '12%',
                    visible: this.$can('patient-cabinet.info'),
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.listFormat([
                            this.$formatter.phoneNumberFormat(value.primary_phone_number),
                            this.$formatter.phoneNumberFormat(value.secondary_phone_number),
                        ]);
                    },
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'email',
                    title: __('Email'),
                    width: '10%',
                    visible: this.$can('patient-cabinet.info'),
                    dataClass: 'no-dash',
                },
                {
                    name: 'location',
                    title: __('Город'),
                    width: '10%',
                    visible: this.$can('patient-cabinet.info'),
                    dataClass: 'no-dash',
                },
                {
                    name: 'clinic_names',
                    filterField: 'clinic',
                    title: __('Клиники'),
                    width: '15%',
                    dataClass: 'no-dash no-ellipsis',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('patients')
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'birthday',
                    sortField: 'birthday',
                    title: __('Дата рождения'),
                    width: '15%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'birthday_range',
                    filter: DateRangeHeaderFilter,
                },
                {
                    name: 'gender',
                    sortField: 'gender',
                    title: __('Пол'),
                    width: '8%',
                    dataClass: 'no-dash text-center',
                    formatter: (value) => {
                        return this.$handbook.getOption('gender_short', value);
                    },
                    filter: 'gender',
                },
                {
                    name: 'source_name',
                    sortField: 'source',
                    filterField: 'source',
                    title: __('Источник информации'),
                    width: '10%',
                    dataClass: 'no-dash',
                    filter: new InformationSourceRepository(),
                },
                {
                    name: 'appointments_count',
                    title: __('Кол-во записей'),
                    width: '10%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'treatment_courses_count',
                    title: __('Кол-во курсов'),
                    width: '10%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'med_insurance',
                    title: __('Наличие мед. страховки'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$handbook.getOption('med_insurance_availability', value);
                    },
                },
                {
                    name: 'is_confirmed',
                    title: __('Личность подтверждена'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'has_registration',
                    title: __('Зарегистрирован в ЛК'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'status',
                    title: __('Статус'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$handbook.getOption('patient_status', value);
                    },
                },
                {
                    name: 'mailing_analysis',
                    title: __('Рез-ты анализов на email'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
            ],
            initialSortOrder: [
                {field: 'full_name', direction: 'asc'},
            ],
            scopes: ['contact_details', 'clinics', 'source', 'count_courses', 'count_appointments'],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
    },
}
</script>
