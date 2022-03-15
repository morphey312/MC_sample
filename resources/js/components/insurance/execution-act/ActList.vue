<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :selectable-rows="true"
        :scopes="scopes"
        table-height="auto"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import InsuranceCompanyActRepository from '@/repositories/insurance-company/act';
import ClinicRepository from '@/repositories/clinic';
import InsuranceCompanyRepository from '@/repositories/insurance-company';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new InsuranceCompanyActRepository({
                limitClinics: this.$isAccessLimited('insurance-acts'),
            }),
            fields: [
                {
                    name: 'clinic_name',
                    sortField: 'clinic',
                    title: __('Клиника'),
                    width: '10%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('insurance-acts'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'insurance_company',
                    title: __('Страховая компания'),
                    width: '15%',
                    filter: new InsuranceCompanyRepository({
                        accessLimit: this.$isAccessLimited('insurance-acts'),
                    }),
                    filterField: 'insurance_company',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'number',
                    title: __('Номер акта'),
                    width: '5%',
                    filter: true,
                },
                {
                    name: 'amount',
                    title: __('Сумма акта'),
                    width: '5%',
                },
                {
                    name: 'created',
                    title: __('Дата формирования'),
                    sortField: 'created',
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                    filterField: 'created',
                },
                {
                    name: 'status',
                    title: __('Статус'),
                    filter: this.$handbook.getOptions('insurance_act_status'),
                    filterField: 'status',
                    sortField: 'status',
                    formatter: (val) => {
                        return this.$handbook.getOption('insurance_act_status', val);
                    },
                    width: '10%',
                },
                {
                    name: 'payment_status',
                    title: __('Оплата'),
                     filter: this.$handbook.getOptions('insurance_act_payment_status'),
                    filterField: 'payment_status',
                    formatter: (val) => {
                        return this.$handbook.getOption('insurance_act_payment_status', val);
                    },
                    width: '10%',
                },
                 {
                    name: 'payment_date',
                    title: __('Дата разнесения платежей'),
                     filter: DateHeaderFilter,
                    filterField: 'payment_date',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    width: '10%',
                },
                {
                    name: 'comment',
                    title: __('Комментарии'),
                    width: '10%',
                },
            ],
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            scopes: [
                'clinic',
                'insurance_company',
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
    }
}
</script>
