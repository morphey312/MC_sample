<template>
    <manage-table style="height:400px; overflow:auto;"
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
import SpecializationRepository from '@/repositories/specialization';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import TimeHeaderFilter from '@/components/general/table/TimeHeaderFilter.vue';
import EmployeeRepository from "@/repositories/employee";
import CONSTANTS from "@/constants";

export default {
    props: {
        patientId: Number,
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
                    name: 'doctor.name',
                    filterField: 'doctor',
                    title: __('Врач'),
                    width: '15%',
                    filter: new EmployeeRepository({filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR}}),
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
            ],
            initialSortOrder: [
                {field: 'date_time', direction: 'desc'},
            ],
            filters: {
                patient: this.patientId,
            },
            scopes: [
                'clinic',
                'patient',
                'doctor',
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
