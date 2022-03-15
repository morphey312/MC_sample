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
import RegistrationRepository from '@/repositories/patient/registration';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new RegistrationRepository(),
            fields: [
                {
                    name: 'full_name',
                    sortField: 'full_name',
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
                    name: 'email',
                    sortField: 'email',
                    title: __('Email'),
                    width: '15%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
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
                {
                    name: 'status',
                    sortField: 'status',
                    title: __('Статус'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('registration_status', value);
                    },
                    filter: 'registration_status',
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
    },
}
</script>