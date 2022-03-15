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
        <template
            slot="patient_name"
            slot-scope="props">
            {{ (props.rowData.patient ? props.rowData.patient.name : '') }}
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import EnquiryServiceRepository from '@/repositories/site-enquiry/service';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS from '@/constants';
import ServiceRepository from '@/repositories/service';
import ClinicRepository from "@/repositories/clinic";
import EmployeeRepository from "@/repositories/employee";

export default {
    props: {
        filters: Object,
    },
    data() {
        let employeeRepo = new EmployeeRepository({
            filters: {
                ever_refunded_site_enquiry: true
            }
        });

        return {
            repository: new EnquiryServiceRepository(),
            fields: [
                {
                    name: 'created_at',
                    title: __('Дата'),
                    width: '5%',
                    sortField: 'created',
                    filterField: 'created_at',
                    filter: DateHeaderFilter,
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                },
                {
                    name: 'site_enquiry.patient.cards',
                    title: __('№ карты'),
                    dataClass: 'no-dash text-select',
                    sortField: 'patient_card_number',
                    filterField: 'patient_card_number',
                    filter: true,
                    width: '5%',
                    formatter: (value) => {
                        return (value && value.length === 0) ? '' : value[0].number;
                    },
                },
                {
                    name: 'site_enquiry.patient.name',
                    title: __('Пациент'),
                    filterField: 'patient_name',
                    sortField: 'patient_name',
                    filter: true,
                    width: '10%',
                },
                {
                    name: 'site_enquiry.name',
                    title: __('Оставил заявку'),
                    filterField: 'enquiry_name',
                    sortField: 'enquiry_name',
                    filter: true,
                    width: '10%',
                },
                {
                    name: 'payed_amount',
                    title: __('Сумма, грн'),
                    dataClass: 'text-right',
                    titleClass: 'text-right',
                    width: '6%',
                    filterField: 'payed_amount',
                    sortField: 'payed_amount',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'service.name',
                    title: __('Услуга'),
                    filterField: 'service',
                    sortField: 'service_name',
                    filter: new ServiceRepository(),
                    filterProps: {
                        multiple: true,
                    },
                    dataClass: 'no-dash',
                },
                {
                    name: 'site_enquiry.order_id',
                    title: __('ID заказа'),
                    filterField: 'enquiry_id',
                    sortField: 'enquiry_id',
                    filter: true,
                    dataClass: 'no-dash text-select',
                    width: '5%',
                },
                {
                    name: 'refund_status',
                    title: __('Возврат3'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString((value === CONSTANTS.SITE_ENQUIRY.SERVICE_REFUND.STATUS_REFUNDED), '<span class="check-yes" />');
                    },
                    sortField: 'refund_status',
                    filter: 'yes_no',
                    filterField: 'is_refund',
                },
                {
                    name: 'site_enquiry.clinic_name',
                    title: __('Клиника заявки'),
                    dataClass: 'no-dash',
                    filter: new ClinicRepository(),
                    filterProps: {
                        multiple: true,
                    },
                    filterField: 'clinic',
                    sortField: 'clinic',
                    width: '10%',
                },
                {
                    name: 'site_enquiry.phone_number',
                    title: __('Телефон в заявке'),
                    filter: true,
                    filterField: 'phone',
                    sortField: 'enquiry_phone',
                    dataClass: 'no-dash text-select',
                    width: '10%',
                },
                {
                    name: 'site_enquiry.email',
                    title: __('Email в заявке'),
                    filter: true,
                    filterField: 'email',
                    sortField: 'enquiry_email',
                    dataClass: 'no-dash text-select',
                    width: '10%',
                },
                {
                    name: 'refunder.full_name',
                    title: __('Вернул'),
                    filter: employeeRepo,
                    filterField: 'refunder',
                    sortField: 'refunder',
                    filterProps: {
                        multiple: true,
                    },
                    dataClass: 'no-dash',
                    width: '10%',
                },
            ],
            employeeRepo,
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            scopes: [
                'site_enquiry',
                'service',
                'refunder',
            ]
        };
    },
    watch: {
        ['filters.clinic'](val) {
            this.employeeRepo.setFilters({
                clinic: val,
                ever_refunded_site_enquiry: true
            })
        },
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
