<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :selectable-rows="true"
        :row-class="addRowClass"
        :flex-height="flexHeight"
        table-height="auto"
        @header-filter-updated="syncFilters"
        @selection-changed="selectionChanged"
        @loaded="loaded">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>


<script>
import CallsRepository from '@/repositories/call';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import CallResultRepository from '@/repositories/calls/result';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import InformationSourceRepository from '@/repositories/patient/information-source';
import CONSTANTS  from '@/constants';

export default {
    props: {
        filters: Object,
        flexHeight: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            repository: new CallsRepository({
                limitClinics: this.$isAccessLimited('calls') && this.$isAccessLimited('appointments'),
            }),
            fields: [
                {
                    name: 'is_patient',
                    title: __('Пациент'),
                    dataClass: 'no-dash',
                    width: '8%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'patient_status',
                },
                {
                    name: 'is_first',
                    sortField: 'is_first',
                    title: __('Первичный'),
                    dataClass: 'no-dash',
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'patient_appointment_is_first',
                },
                {
                    name: 'operator_name',
                    sortField: 'operator_name',
                    filterField: 'operator',
                    title: __('Оператор'),
                    width: '15%',
                    filter: new EmployeeRepository(),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'source',
                    title: __('Источник'),
                    width: '15%',
                    dataClass: 'no-dash',
                    filter: new InformationSourceRepository(),
                    filterField: 'patient_source',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'date_time',
                    sortField: 'date_time',
                    filterField: 'date',
                    title: __('Дата и время'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'contact_name',
                    filterField: 'patient_name',
                    title: __('Контакт'),
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'phone_number',
                    title: __('Номер телефона'),
                    filterField: 'patient_phone_number',
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(
                            value.map(v => this.$formatter.phoneNumberFormat(v))
                        );
                    },
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'card_number',
                    filterField: 'patient_card_number',
                    title: __('Номер карты'),
                    width: '10%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'specialization_name',
                    sortField: 'specialization_name',
                    filterField: 'specialization',
                    title: __('Специализация'),
                    width: '15%',
                    filter: new SpecializationRepository({
                        limitClinics: this.$isAccessLimited('calls') && this.$isAccessLimited('appointments'),
                    }),
                    filterProps: {
                        limit: 100,
                        multiple: true,
                    },
                },
                {
                    name: 'call_result_name',
                    sortField: 'result_name',
                    filterField: 'call_result',
                    title: __('Результат'),
                    width: '15%',
                    filter: new CallResultRepository(),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'clinic_name',
                    sortField: 'clinic_name',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '15%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('calls') && this.$isAccessLimited('appointments'),
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'created_at',
                    sortField: 'created_at',
                    title: __('Дата создания'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
            ],
            initialSortOrder: [
                {field: 'date_time', direction: 'desc'},
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
        addRowClass(item, index) {
            return item.call_delete_reason_id ? 'deleted-row' : '';
        },
    },
};
</script>
