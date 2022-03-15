<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :selectable-rows="true"
        :row-class="addRowClass"
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
import AppointmentRepository from '@/repositories/appointment';
import ServiceRepository from '@/repositories/service';
import SpecializationRepository from '@/repositories/specialization';
import AppointmentStatusRepository from '@/repositories/appointment/status';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import TimeHeaderFilter from '@/components/general/table/TimeHeaderFilter.vue';
import EmployeeRepository from '@/repositories/employee';
import InformationSourceRepository from '@/repositories/patient/information-source';
import AppointmentStatusReasonRepository from '@/repositories/appointment/status/reason';
import CONSTANTS  from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new AppointmentRepository({
                limitClinics: this.$isAccessLimited('calls') && this.$isAccessLimited('appointments'),
            }),
            fields: [
                {
                    name: 'patient.is_patient',
                    sortField: 'is_patient',
                    filterField: 'is_patient',
                    title: __('Пациент'),
                    dataClass: 'no-dash',
                    width: '7%',
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
                    name: 'clinic_name',
                    sortField: 'clinic_name',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '10%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('calls') && this.$isAccessLimited('appointments'),
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'date',
                    sortField: 'date',
                    title: __('Дата записи'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                    filterField: 'date',
                },
                {
                    name: 'start',
                    sortField: 'start',
                    title: __('Начало приема'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.timeFormat(this.$moment(value, 'HH:mm:ss'));
                    },
                    filter: TimeHeaderFilter,
                    filterField: 'timeStart',
                },
                {
                    name: 'end',
                    sortField: 'end',
                    title: __('Окончание приема'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.timeFormat(this.$moment(value, 'HH:mm:ss'));
                    },
                    filter: TimeHeaderFilter,
                    filterField: 'timeEnd',
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
                    scopes: ['card_number'],
                },
                {
                    name: 'patient.full_name',
                    sortField: 'patient_name',
                    filterField: 'patient_name',
                    title: __('Пациент'),
                    width: '18%',
                    filter: true,
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
                        multiple: true,
                    },
                    scopes: ['specialization'],
                },
                {
                    name: 'doctor.name',
                    sortField: 'doctor_name',
                    title: __('Врач'),
                    width: '15%',
                    filterField: 'doctor',
                    filter: new EmployeeRepository({
                        filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR},
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'service_names',
                    filterField: 'services',
                    title: __('Услуги'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new ServiceRepository(),
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['appointment_services'],
                },
                {
                    name: 'status.name',
                    sortField: 'statusName',
                    title: __('Статус'),
                    width: '10%',
                    filter: new AppointmentStatusRepository(),
                    filterField: 'status',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['status'],
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    width: '20%',
                    filter: true,
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
                    scopes: ['operator'],
                },
                {
                    name: 'creator_name',
                    sortField: 'creator_name',
                    filterField: 'creator',
                    title: __('Оператор (завел)'),
                    width: '15%',
                    filter: new EmployeeRepository(),
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['creator'],
                },
                {
                    name: 'source_name',
                    sortField: 'source',
                    title: __('Источник (запись)'),
                    width: '15%',
                    dataClass: 'no-dash',
                    filter: new InformationSourceRepository(),
                    filterField: 'source',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['source'],
                },
                {
                    name: 'patient.source_name',
                    title: __('Источник (пациент)'),
                    width: '15%',
                    dataClass: 'no-dash',
                    filter: new InformationSourceRepository(),
                    filterField: 'patient_source',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['patient_source'],
                },
                {
                    name: 'insurance_policy_id',
                    filterField: 'insurance_policy',
                    title: __('Полис в записи'),
                    width: '14%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
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
                {
                    name: 'status_reason',
                    filterField: 'status_reason',
                    sortField: 'status_reason',
                    title: __('Причина изм. статуса'),
                    width: '10%',
                    filter: new AppointmentStatusReasonRepository(),
                    scopes: ['status_reason'],
                },
                {
                    name: 'status_reason_comment',
                    filterField: 'status_reason_comment',
                    sortField: 'status_reason_comment',
                    title: __('Прим. к смене статуса'),
                    width: '12%',
                    filter: true,
                    scopes: ['status_reason'],
                },
            ],
            initialSortOrder: [
                {field: 'date_time', direction: 'desc'},
            ],
            scopes: [
                'clinic',
                'doctor',
                'patient',
                'patient_contacts',
                'card_specialization',
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
            return item.is_deleted == 1 ? 'deleted-row' : '';
        },
    },
};
</script>
