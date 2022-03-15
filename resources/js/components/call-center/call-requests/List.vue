<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :selectable-rows="true"
        observe-changes="CallRequest"
        @selection-changed="selectionChanged"
        @header-filter-updated="syncFilters">
    </manage-table>
</template>


<script>
import CallRequestRepository from '@/repositories/call-request';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import ClinicRepository from '@/repositories/clinic';
import CallRequestPurposeRepository from '@/repositories/call-request/purpose';
import CallResultRepository from '@/repositories/calls/result';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new CallRequestRepository(),
            fields: [
                {
                    name: 'status',
                    sortField: 'status',
                    title: __('Статус заявки'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$handbook.getOption('call_request_status', value);
                    },
                    filter: 'call_request_status',
                },
                {
                    name: 'patient.full_name',
                    sortField: 'patient_name',
                    filterField: 'patient_name',
                    title: __('Пациент'),
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'patient.contact_details.primary_phone_number',
                    title: __('Тел. пациента'),
                    width: '10%',
                    filter: true,
                    filterField: 'patient_phone_number',
                },
                {
                    name: 'patient.contact_details.email',
                    title: __('Email пациента'),
                    width: '10%',
                    filter: true,
                    filterField: 'patient_email',
                },
                {
                    name: 'call_request_purpose',
                    sortField: 'purpose_name',
                    filterField: 'purpose',
                    title: __('Цель прозвона'),
                    width: '15%',
                    filter: new CallRequestPurposeRepository(),
                },
                {
                    name: 'comment',
                    sortField: 'comment',
                    title: __('Примечание к заявке'),
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'recommended_appointment_date',
                    sortField: 'recommended_appointment_date',
                    title: __('Рекомм. Дата записи'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'recall_period',
                    sortField: 'recall_from',
                    title: __('Дата прозвона'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.daterangeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'call.call_result_name',
                    sortField: 'call_result',
                    filterField: 'call_result',
                    title: __('Результат звонка'),
                    width: '15%',
                    filter: new CallResultRepository(),
                },
                {
                    name: 'clinic_name',
                    sortField: 'clinic_name',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '15%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('call-requests'),
                    }),
                },
                {
                    name: 'specialization_name',
                    sortField: 'specialization_name',
                    filterField: 'specialization',
                    title: __('Специализация'),
                    width: '15%',
                    filter: new SpecializationRepository({
                        limitClinics: this.$isAccessLimited('call-requests'),
                    }),
                },
                {
                    name: 'doctor_name',
                    sortField: 'doctor_name',
                    filterField: 'doctor',
                    title: __('Врач'),
                    width: '15%',
                    filter: new EmployeeRepository({
                        filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR},
                    }),
                },
                {
                    name: 'appointment.date',
                    sortField: 'appointment_date',
                    filterField: 'appointment_date',
                    title: __('Дата записи'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'appointment.operator_name',
                    sortField: 'appointment_operator_name',
                    filterField: 'appointment_operator',
                    title: __('Оператор'),
                    width: '15%',
                    filter: new EmployeeRepository({
                        filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR},
                    }),
                },
                {
                    name: 'comment_canceled',
                    sortField: 'comment_canceled',
                    title: __('Примечание к отмене'),
                    width: '20%',
                    filter: true,
                },
            ],
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
            scopes: [
                'call',
                'appointment',
                'patient',
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
    },
};
</script>
