<template>
    <manage-table
        ref="table"
        :fields="fields"
        :scopes="scopes"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :selectable-rows="true"
        :row-class="addRowClass"
        :flex-height="true"
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
import AppointmentStatusRepository from '@/repositories/appointment/status';
import ServiceRepository from '@/repositories/service';
import SpecializationRepository from '@/repositories/specialization';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import TimeHeaderFilter from '@/components/general/table/TimeHeaderFilter.vue';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS  from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new AppointmentRepository(),
            fields: [
                {
                    name: 'clinic_name',
                    sortField: 'clinic_name',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '10%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('appointments'),
                    }),
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
                    scopes: ['card_number'],
                },
                {
                    name: 'specialization_name',
                    sortField: 'specialization_name',
                    filterField: 'specialization',
                    title: __('Специализация'),
                    width: '15%',
                    filter: new SpecializationRepository({
                        limitClinics: this.$isAccessLimited('appointments'),
                    }),
                    scopes: ['specialization'],
                },
                {
                    name: 'status.name',
                    sortField: 'status_name',
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
                    name: 'service_names',
                    filterField: 'service_name',
                    title: __('Услуги'),
                    width: '25%',
                    formatter: (values) => {
                        var unique = [];

                        values.forEach(value => {
                            if (!unique.includes(value))
                                unique.push((value))
                        })

                        return this.$formatter.listFormat(unique);
                    },
                    filter: true,
                    scopes: ['appointment_services'],
                },
                {
                    name: 'doctor.name',
                    filterField: 'doctor',
                    title: __('Врач'),
                    width: '15%',
                    filter: new EmployeeRepository({filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR}}),
                },
                {
                    name: 'operator_name',
                    sortField: 'operator_name',
                    filterField: 'operator',
                    title: __('Оператор'),
                    width: '15%',
                    filter: new EmployeeRepository({filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR}}),
                    scopes: ['operator'],
                },
                {
                    name: 'creator_name',
                    sortField: 'creator_name',
                    filterField: 'creator',
                    title: __('Оператор (завел)'),
                    width: '15%',
                    filter: new EmployeeRepository({filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR}}),
                    scopes: ['creator'],
                },
                {
                    name: 'insurance_policy_id',
                    title: __('Полис'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filterField: 'insurance_policy',
                    filter: 'yes_no',
                },
            ],
            initialSortOrder: [
                {field: 'date_time', direction: 'desc'},
            ],
            scopes: [
                'clinic',
                'doctor',
                'patient',
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
