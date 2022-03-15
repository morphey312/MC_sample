<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :selectable-rows="true"
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
import AppointmentServiceRepository from '@/repositories/appointment/service';
import ProxyRepository from '@/repositories/proxy-repository';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repo = new AppointmentServiceRepository();
                return repo.fetchInsuranceExportList(filters, sort, null, page, limit);
            }),
            fields: [
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    width: '100px',
                    filter: true,
                    sortField:'clinic_name',
                    filterField: 'clinic_name',
                },
                {
                    name: 'insurance_company_name',
                    title: __('Страховая компания'),
                    width: '100px',
                    filter: true,
                    sortField:'insurance_company_name',
                    filterField: 'insurance_company_name',
                },
                {
                    name: 'patient_name',
                    title: __('Пациент'),
                    width: '250px',
                    filter: true,
                    sortField:'patient_name',
                    filterField: 'patient_name',
                },
                {
                    name: 'policy_number',
                    title: __('Номер полиса'),
                    width: '90px',
                },
                {
                    name: 'patient_card',
                    title: __('Номер карты'),
                    width: '90px',
                    filterField: 'patient_card_number',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'appointment_date',
                    title: __('Дата записи'),
                    width: '90px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filterField: 'appointment_date',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'cost',
                    title: __('Стоимость по прайсу'),
                    width: '90px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'franchise',
                    title: __('Франшиза, %'),
                    width: '90px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'payed',
                    title: __('Оплачено, грн'),
                    width: '90px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'quantity',
                    title: __('Количество услуг'),
                    width: '90px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'service_name_detailed',
                    title: __('Услуга'),
                    width: '350px',
                },
                {
                    name: 'appointment.diagnosis',
                    title: __('Диагноз'),
                    width: '250px',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    }
                },
                {
                    name: 'act_service.number',
                    title: __('Номер акта'),
                    width: '90px',
                },
            ],
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
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
    },
}
</script>
