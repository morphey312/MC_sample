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
            repository: new ProxyRepository(({filters, sort, page, limit}) => {
                let repo = new AppointmentServiceRepository();
                let scopes = ['note'];
                return repo.fetchInsuranceExportList(filters, sort, scopes, page, limit);
            }),
            fields: [
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
                    name: 'note.task',
                    title: __('Задача'),
                    width: '300px',
                },
                {
                    name: 'note.note',
                    title: __('Примечание'),
                    width: '300px',
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
