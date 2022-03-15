<template>
    <manage-table 
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        @header-filter-updated="syncFilters">
    </manage-table>
</template>


<script>
import CallLogRepository from '@/repositories/calls/call-log';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS  from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new CallLogRepository(),
            fields: [
                {
                    name: 'patient.full_name',
                    sortField: 'patient_name',
                    filterField: 'patient_name',
                    title: __('Пациент'),
                    width: '10%',
                    filter: true,
                },
                {
                    name: 'type',
                    sortField: 'type',
                    title: __('Тип'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$handbook.getOption('call_log_type', value);
                    },
                    filter: 'call_log_type',
                },
                {
                    name: 'phone_number',
                    sortField: 'phone_number',
                    title: __('Номер телефона'),
                    width: '10%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'started_at',
                    sortField: 'started_at',
                    title: __('Начало звонка'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'ended_at',
                    sortField: 'ended_at',
                    title: __('Окончание звонка'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'source',
                    sortField: 'source',
                    title: __('Источник'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$handbook.getOption('call_log_source', value);
                    },
                    filter: 'call_log_source',
                },
                {
                    name: 'clinic.name',
                    sortField: 'clinic_name',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '10%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('call-logs'),
                    }),
                },
                {
                    name: 'status',
                    sortField: 'status',
                    title: __('Результат'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$handbook.getOption('call_log_status', value);
                    },
                    filter: 'call_log_status',
                },
                {
                    name: 'process.operator.name',
                    sortField: 'operator_name',
                    filterField: 'operator',
                    title: __('Оператор'),
                    width: '10%',
                    filter: new EmployeeRepository({
                        filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR},
                    }),
                },
                {
                    name: 'process.sip',
                    sortField: 'sip_number',
                    filterField: 'sip_number',
                    title: 'SIP',
                    width: '5%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'queue',
                    sortField: 'queue',
                    filterField: 'queue',
                    title: __('Очередь'),
                    width: '10%',
                    filter: 'voip_queue',
                },
                {
                    name: 'extension',
                    sortField: 'extension',
                    filterField: 'extension',
                    title: __('Линия'),
                    width: '10%',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
            ],
            initialSortOrder: [
                {field: 'started_at', direction: 'desc'},
            ],
            scopes: [
                'default',
                'patient',
                'process',
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
    },
};
</script>