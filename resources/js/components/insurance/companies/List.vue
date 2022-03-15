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
import InsuranceCompanyRepository from '@/repositories/insurance-company';
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new InsuranceCompanyRepository({
                limitClinics: this.$isAccessLimited('insurance')
            }),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Полное название'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'short_name',
                    sortField: 'short_name',
                    filterField: 'short_name',
                    filter: true,
                    title: __('Краткое название'),
                    width: '20%',
                },
                {
                    name: 'location',
                    sortField: 'location',
                    filterField: 'location',
                    filter: true,
                    title: __('Юридический адрес'),
                    width: '20%',
                },
                {
                    name: 'post_address',
                    sortField: 'post_address',
                    filterField: 'post_address',
                    filter: true,
                    title: __('Почтовый адрес'),
                    width: '20%',
                },
                {
                    name: 'bank_account',
                    sortField: 'bank_account',
                    filterField: 'bank_account',
                    filter: true,
                    title: __('Расчетный счет'),
                    width: '20%',
                },
                {
                    name: 'phone_number',
                    sortField: 'phone_number',
                    filterField: 'phone_number',
                    filter: true,
                    title: __('Контактный телефон'),
                    width: '20%',
                },
                {
                    name: 'egrpo',
                    sortField: 'egrpo',
                    title: __('ЕГРПОУ(ОКПО)'),
                    filterField: 'egrpo',
                    filter: true,
                    width: '20%',
                },
                {
                    name: 'tax_number',
                    sortField: 'tax_number',
                    filterField: 'tax_number',
                    filter: true,
                    title: __('ИНН'),
                    width: '20%',
                },
                {
                    name: 'signer',
                    sortField: 'signer',
                    filterField: 'signer',
                    filter: true,
                    title: __('Подписант'),
                    width: '20%',
                },
                {
                    name: 'agreements',
                    sortField: 'agreements',
                    filterField: 'agreements',
                    filter: true,
                    title: __('Договор'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                },
                {
                    name: 'clinic_names',
                    title: __('Клиники'),
                    width: '20%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('insurance')
                    }),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filterProps: {
                        multiple: true,
                    },
                    filterField: 'clinic',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
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
            this.$emit('header-filter-updated', updates);
        },
    }
}
</script>
