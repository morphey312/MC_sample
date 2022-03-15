<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="flexHeight"
        @selection-changed="selectionChanged"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import WaitListRecordRepository from '@/repositories/wait-list-record';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import EmployeeRepository from '@/repositories/employee';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import CONSTANTS from '@/constants';

export default {
    props: {
        flexHeight: {
            type: Boolean,
            default: false,
        },
        filters: Object,
    },
    data() {
        return {
            repository: new WaitListRecordRepository(),
            fields: [
                {
                    name: 'status',
                    sortField: 'status',
                    title: __('Статус'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$handbook.getOption('wait_list_record_status', value);
                    },
                    filter: 'wait_list_record_status',
                },
                {
                    name: 'name',
                    title: __('Имя'),
                    width: '15%',
                    filter: true,
                    filterField: 'patient_name',
                },
                {
                    name: 'is_first_visit_call',
                    sortField: 'isFirstVisitCall',
                    title: __('Тип пациента (звонок)'),
                    dataClass: 'no-dash',
                    width: '10%',
                    formatter: (value) => {
                        return this.$handbook.getOption('patient_appointment_is_first', value);
                    },
                    filter: 'patient_appointment_is_first',
                    filterField: 'isFirstVisitCall'
                },
                {
                    name: 'is_first_visit_process',
                    sortField: 'isFirstVisitProcess',
                    title: __('Тип пациента (запись)'),
                    dataClass: 'no-dash',
                    width: '10%',
                    formatter: (value) => {
                        return this.$handbook.getOption('patient_appointment_is_first', value);
                    },
                    filter: 'patient_appointment_is_first',
                    filterField: 'isFirstVisitProcess'
                },
                {
                    name: 'phone_number',
                    title: __('Номер телефона'),
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                    width: '15%',
                    filter: true,
                    filterField: 'patient_phone_number',
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'period_range',
                    title: __('Период резерва'),
                    width: '12%',
                },
                {
                    name: 'clinic_name',
                    sortField: 'clinic_name',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '10%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('wait-list-record'),
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'specialization_name',
                    filterField: 'specialization',
                    title: __('Специализация'),
                    width: '10%',
                    filter: new SpecializationRepository({
                        accessLimit: this.$isAccessLimited('wait-list-record'),
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'doctor_name',
                    title: __('Врач'),
                    width: '10%',
                    filterField: 'doctor',
                    filter: new EmployeeRepository({
                        filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR},
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'operator_name',
                    title: __('Оператор'),
                    width: '12%',
                    filterField: 'operator',
                    filter: new EmployeeRepository({
                        filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR},
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'creator_name',
                    title: __('Кто создал'),
                    width: '12%',
                    filterField: 'creator',
                    filter: new EmployeeRepository({
                        filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR},
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'cancel_reason',
                    title: __('Причина отмены записи'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$handbook.getOption('wait_list_record_cancel_reason', value);
                    },
                    filterField: 'cancel_reason',
                    filter: 'wait_list_record_cancel_reason',
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
                    name: 'process.started_at',
                    sortField: 'started_at',
                    filterField: 'started_at',
                    title: __('Дата получения заявки оператором'),
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
                    title: __('Дата зав. обработки'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'process.status',
                    sortField: 'process_status',
                    filterField: 'process_status',
                    title: __('Статус'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('call_process_status', value);
                    },
                    filter: 'call_process_status',
                },
                {
                    name: 'process.status_comment',
                    filterField: 'process_status_comment',
                    title: __('Примечание к обработке'),
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'auto_process',
                    title: __('Автоматическое завершение'),
                    width: '20%',
                    formatter: (value) => {
                        return value !== null 
                            ? [
                                value.operator_name,
                                ', ', 
                                this.$formatter.datetimeFormat(value.started_at),
                                '&mdash;',
                                this.$formatter.datetimeFormat(value.processed_at),
                            ].join('')
                            : null;
                    },
                },
            ],
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
            scopes: [
                'call',
                'patient',
                'clinic',
                'operator',
                'doctor',
                'created_by',
                'specialization',
                'process',
                'auto_process',
                'prepayment_service',
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
