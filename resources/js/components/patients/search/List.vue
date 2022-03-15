<template>
    <manage-table
        ref="table"
        :filters="filters" 
        :fields="fields"
        :scopes="scopes"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        :enable-pagination="false"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot="patient_details"
            slot-scope="props" >
            <patient-details 
                :model="props.rowData"
                @selected="selected"
            />
        </template>
    </manage-table>
</template>

<script>
import PatientRepository from '@/repositories/patient';
import ClinicRepository from '@/repositories/clinic';
import ProxyRepository from '@/repositories/proxy-repository';
import InformationSourceRepository from '@/repositories/patient/information-source';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import PatientDetails from './Details.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        PatientDetails,
    },
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, scopes}) => {
                let repository = new PatientRepository();
                return repository.fetch(filters, sort, scopes, 1, 200);
            }),
            fields: [
                {
                    name: 'patient_details',
                    sortField: 'full_name',
                    filterField: 'full_name',
                    title: __('Пациент'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'med_insurance',
                    sortField: 'med_insurance',
                    filterField: 'med_insurance',
                    title: __('Есть страховка'),
                    dataClass: 'no-dash',
                    width: '10%',
                    formatter: (value) => {
                        let boolVal = (value === CONSTANTS.PATIENT.MED_INSURANCE.HAS_INSURANCE ? true : false);
                        return this.$formatter.boolToString(boolVal, '<span class="check-yes" />');
                    },
                    filter: 'med_insurance_availability',
                },
                {
                    name: 'primary_phone_number',
                    title: __('Телефон'),
                    filterField: 'primary_phone_number',
                    width: '9%',
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'secondary_phone_number',
                    title: __('Доп. телефон'),
                    filterField: 'secondary_phone_number',
                    width: '9%',
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'clinic_names',
                    title: __('Клиники'),
                    filterField: 'clinic',
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new ClinicRepository(),
                },
                {
                    name: 'birthday',
                    sortField: 'birthday',
                    filterField: 'birthday',
                    title: __('Дата рождения'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'gender',
                    sortField: 'gender',
                    title: __('Пол'),
                    width: '5%',
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
                    width: '13%',
                    dataClass: 'no-dash',
                    filter: new InformationSourceRepository(),
                },
                {
                    name: 'comment',
                    sortField: 'comment',
                    title: __('Примечание'),
                    width: '14%',
                },
            ],
            initialSortOrder: [
                {field: 'full_name', direction: 'asc'},
            ],
            scopes: ['contact_details', 'clinics', 'source'],
        };
    },
    methods: {
        selected(patient) {
            this.$emit('selected', patient);
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
            this.$emit('rows-count', this.getRowsCount());
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        getRowsCount() {
            return this.$refs.table.getRowsCount();
        },
    },
}
</script>