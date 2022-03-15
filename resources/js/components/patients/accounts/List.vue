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
        <template slot="patient" slot-scope="props">
            <template v-if="props.rowData.patient === null">
                {{ __('Не привязан') }}
            </template>
            <a
                v-else-if="$canUpdate('patients')"
                href="#"
                @click.prevent="showPatient(props.rowData.patient)">
                {{ props.rowData.patient.full_name }}
            </a>
            <template v-else>
                {{ props.rowData.patient.full_name }}
            </template>
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import PatientUserRepository from '@/repositories/patient/user';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import PatientCreateMixin from '@/components/patients/mixins/patient-create';

export default {
    mixins: [
        PatientCreateMixin,
    ],
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new PatientUserRepository(),
            fields: [
                {
                    name: 'patient',
                    sortField: 'patient_name',
                    filterField: 'full_name',
                    title: __('ФИО'),
                    filter: true,
                },
                {
                    name: 'phone',
                    sortField: 'phone',
                    title: __('Телефон'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value); 
                    },
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'patient.email',
                    sortField: 'email',
                    filterField: 'email',
                    title: __('Email'),
                    width: '15%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'patient.birthday',
                    sortField: 'birthday',
                    filterField: 'birthday',
                    title: __('Дата рождения'),
                    width: '15%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'created_at',
                    sortField: 'created_at',
                    title: __('Дата регистрации'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
            ],
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
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
        showPatient(patient) {
            this.displayEditPatientForm(patient.id, (patient) => {
                this.refresh();
            });
        },
    },
}
</script>