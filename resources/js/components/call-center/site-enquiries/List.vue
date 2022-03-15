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
        @header-filter-updated="syncFilters">
    </manage-table>
</template>

<script>
import SiteEnquiryRepository from '@/repositories/site-enquiry';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import EmployeeRepository from '@/repositories/employee';
import ClinicRepository from '@/repositories/clinic';
import CONSTANTS from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new SiteEnquiryRepository(),
            fields: [
                {
                    name: 'status',
                    sortField: 'status',
                    title: __('Статус'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$handbook.getOption('enquiry_status', value);
                    },
                    filter: 'enquiry_status',
                },
                {
                    name: 'category',
                    sortField: 'category',
                    title: __('Тип'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$handbook.getOption('enquiry_type', value);
                    },
                    filter: 'enquiry_type',
                },
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Имя'),
                    width: '15%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'phone_number',
                    sortField: 'phone_number',
                    title: __('Номер телефона'),
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                    width: '15%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'email',
                    sortField: 'email',
                    title: 'Email',
                    width: '15%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'clinic_name',
                    sortField: 'clinic_name',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '10%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('site-enquiries'),
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'notes',
                    title: __('Комментарий'),
                    width: '24%',
                    filter: true,
                },
                {
                    name: 'referer',
                    title: __('Сайт'),
                    width: '12%',
                    filter: true,
                },
                {
                    name: 'operator_name',
                    sortField: 'operator_name',
                    filterField: 'operator',
                    title: __('Оператор'),
                    width: '12%',
                    filter: new EmployeeRepository({
                        filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR},
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'created_at',
                    sortField: 'created_at',
                    title: __('Дата создания'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'process.processed_at',
                    sortField: 'processed_at',
                    filterField: 'processed_at',
                    title: __('Дата обработки'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                // {
                //     name: 'process.status',
                //     sortField: 'process_status',
                //     filterField: 'process_status',
                //     title: __('Статус'),
                //     width: '15%',
                //     formatter: (value) => {
                //         return this.$handbook.getOption('call_process_status', value);
                //     },
                //     filter: 'call_process_status',
                // },
                {
                    name: 'process.status_comment',
                    filterField: 'process_status_comment',
                    title: __('Примечание к обработке'),
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'order_id',
                    filterField: 'order_id',
                    title: __('Номер заказа'),
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'payment_status',
                    filterField: 'payment_status',
                    title: __('Статус оплаты'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('enquiry_payment_status', value);
                    },
                    filter: 'enquiry_payment_status',
                },
            ],
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
            scopes: [
                'default',
                'patient',
                'operator',
                'process',
                'services',
                'payed',
            ],
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
    },
};
</script>
