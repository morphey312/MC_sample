<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :flex-height="true"
        @header-filter-updated="syncFilters">
        <template
            slot-scope="props"
            slot="name">
            <a
                href="#"
                @click.prevent="showDetails(props.rowData)">
                {{ courseName(props.rowData) }}
            </a>
        </template>
    </manage-table>
</template>
<script>
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import Details from './Details.vue';
import TreatmentCourseRepository from '@/repositories/treatment-course';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS  from '@/constants';

export default {
    props: {
        filters: Object,
        patient: Object,
    },
    data() {
        return {
            repository: new TreatmentCourseRepository(),
            fields: [
                {
                    name: 'name',
                    title: __('Название курса лечения'),
                    filterField: 'name',
                    width: '35%',
                    filter: true,
                },
                {
                    name: 'start',
                    title: __('Дата начала'),
                    sortField: 'date_start',
                    filterField: 'date_start',
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'end',
                    title: __('Дата окончания'),
                    sortField: 'date_end',
                    filterField: 'date_end',
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'specialization_name',
                    title: __('Специализация'),
                    sortField: 'specialization',
                    width: '15%',
                    filterField: 'specialization',
                    filter: new SpecializationRepository({filters: {id: this.getSpecializationIds()}}),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'doctor_name',
                    filterField: 'doctor',
                    sortField: 'doctor',
                    title: __('Врач'),
                    width: '20%',
                    filter: new EmployeeRepository({
                        filters: {
                            positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                            has_patient_appointment: this.patient.id,
                        },
                    }),
                },
            ],
            initialSortOrder: [
                {field: 'date_start', direction: 'desc'},
            ],
        };
    },
    methods: {
        getSpecializationIds() {
            if (this.$store.state.user.isDoctor) {
                return this.$store.state.user.specializations;
            }
            return this.patient.cardsSpecializations;
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        courseName(row) {
            let name = null;
            for (let appointment of row.appointments) {
                for (let service of appointment.services) {
                    if (service.is_base === true) {
                        name = service.name;
                        return name;
                    }
                }
            }
            return name;
        },
        showDetails(row) {
            this.$modalComponent(Details, {
                course: row,
                patient: this.patient,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Курс лечения'),
                width: '1030px',
            });
        },
    },
}
</script>
