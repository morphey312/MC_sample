<template>
    <manage-table 
        ref="table"
        :fields="fields"
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
import CallsRepository from '@/repositories/call';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import CallResultRepository from '@/repositories/calls/result';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS  from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new CallsRepository(),
            fields: [
                {
                    name: 'patient.is_patient',
                    sortField: 'is_patient',
                    filterField: 'is_patient',
                    title: __('Пациент'),
                    dataClass: 'no-dash',
                    width: '10%',
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
                    name: 'date_time',
                    sortField: 'date_time',
                    filterField: 'date',
                    title: __('Дата и время'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'card_number',
                    filterField: 'patient_card_number',
                    title: __('Номер карты'),
                    width: '10%',
                    filter: true,
                },
                {
                    name: 'specialization_name',
                    sortField: 'specialization_name',
                    filterField: 'specialization',
                    title: __('Специализация'),
                    width: '15%',
                    filter: new SpecializationRepository({
                        limitClinics: this.$isAccessLimited('calls'),
                    }),
                },
                {
                    name: 'clinic_name',
                    sortField: 'clinic_name',
                    filterField: 'clinic',
                    title: __('Клиника'),
                    width: '15%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('calls'),
                    }),
                },
                {
                    name: 'call_result_name',
                    sortField: 'result_name',
                    filterField: 'call_result',
                    title: __('Результат'),
                    width: '15%',
                    filter: new CallResultRepository(),
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
                    filter: new EmployeeRepository({filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR}}),
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