<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :scopes="scopes"
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
                    name: 'last_name',
                    title: __('Фамилия'),
                    width: '15%',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'first_name',
                    title: __('Имя'),
                    width: '15%',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'second_name',
                    title: __('Отчество'),
                    width: '15%',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'second_name',
                    title: __('Место рождения'),
                    width: '15%',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'second_name',
                    title: __('Синхронизировано с МЦ+'),
                    width: '15%',
                    dataClass: 'no-ellipsis',
                },
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
    },
}
</script>
