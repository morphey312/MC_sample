<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :row-class="onRowClass"
        :flex-height="true"
        :enable-pagination="false"
        :multiple-selectable="true"
        @selection-changed="checked"
        @header-filter-updated="syncFilters">
        <template slot="doctor-info" slot-scope="props">
            <doctor-info :model="props.rowData" />
        </template>
    </manage-table>
</template>

<script>
import DaySheetRepository from '@/repositories/day-sheet';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import ProxyRepository from '@/repositories/proxy-repository';
import DoctorInfo from './DoctorInfo.vue';
import TimeHeaderFilter from '@/components/general/table/TimeHeaderFilter.vue';

export default {
    components: {
        DoctorInfo
    },
    props: {
        filters: Object,
    },
    data() {
        return {
            rows: [],
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repository = new DaySheetRepository();
                return repository.fetch(filters, sort, scopes, page, limit).then((result) => {
                    return {
                        rows: this.prepareRows(result.rows),
                        pagination: result.pagination,
                    }
                });
            }),
            fields: [
                {
                    name: '__checkbox',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    width: '22px',
                },
                {
                    name: 'weekDay',
                    title: __('День'),
                    sortField: 'date',
                    width: '55px',
                    filter: this.weekDays(),
                    filterField: 'weekDay',
                },
                {
                    name: 'date',
                    title: __('Дата'),
                    sortField: 'date',
                    width: '90px',
                    dataClass: 'no-ellipsis',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    }
                },
                {
                    name: 'doctor-info',
                    title: __('Врач / Процедурный кабинет'),
                    sortField: 'owner_name',
                    width: '160px',
                    filter: true,
                    filterField: 'owner_name',
                },
                {
                    name: 'time_from',
                    title: __('Начало работы'),
                    sortField: 'time_start',
                    width: '90px',
                    formatter: (value) => {
                        return this.$moment(value, 'HH:mm:ss').format('HH:mm');
                    },
                    filter: TimeHeaderFilter,
                    filterField: 'timeFrom',
                },
                {
                    name: 'time_to',
                    title: __('Конец работы'),
                    sortField: 'time_end',
                    width: '90px',
                    formatter: (value) => {
                        return this.$moment(value, 'HH:mm:ss').format('HH:mm');
                    },
                    filter: TimeHeaderFilter,
                    filterField: 'timeTo',
                },
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    sortField: 'clinic',
                    width: '200px',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('appointments-sheets'),
                    }),
                    filterField: 'clinic',
                },
                {
                    name: 'day_sheet_specializations',
                    title: __('Специализация'),
                    dataClass: 'no-ellipsis',
                    width: "200px",
                    filter: new SpecializationRepository({
                        limitClinics: this.$isAccessLimited('appointments-sheets'),
                    }),
                    filterField: 'specialization',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'workday_duration',
                    title: __('Длительность рабочего дня'),
                    width: "95px",
                },
                {
                    name: 'appointments_duration',
                    title: __('Длительность записи'),
                    width: "100px",
                },
                {
                    name: 'doctor.appointments_count',
                    title: __('Кол-во записавшихся'),
                    width: "100px",
                },
                {
                    name: 'doctor.appointments_day_first',
                    title: __('Кол-во первичных пациентов'),
                    width: "82px",
                },
                {
                    name: 'doctor.appointments_day_repeated',
                    title: __('Кол-во повторных пациентов'),
                    width: "85px",
                },
                {
                    name: 'workspace.name',
                    title: __('Кабинет'),
                    dataClass: 'no-ellipsis',
                    width: "80px",
                    filter: true,
                    filterField: 'workspace_name',
                }
            ],
            initialSortOrder: [
                {field: 'time_start', direction: 'asc'},
            ],
            scopes: [
                'default',
                'owner',
                'appointments',
                'limitations',
            ],
        }
    },
    methods: {
        prepareRows(rows) {
            return rows.map(row => {
                row.workday_duration = this.$formatter.timeTotal(row.doctor.time_sheets, 'time_from', 'time_to');
                row.appointments_duration = this.$formatter.timeTotal(row.doctor.appointment_durations, 'start', 'end');
                return row;
            });
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        checked(rows = []) {
            this.rows = rows;
            this.$emit('selection-changed', rows);
        },
        onRowClass(dataItem, index) {
            let className = [];

            if (this.rows.indexOf(dataItem.id) !== -1) {
                className.push('selected-table-row');
            } 

            if (dataItem.workday_duration <= dataItem.appointments_duration) {
                className.push('bg-danger');
            }
            return className;
        },
        getSelectedRows() {
            let selected = this.$refs.table.getSelectedRows();
            let list = this.$refs.table.getData();
            let rows = [];

            list.forEach((item) => {
                if (selected.indexOf(item.id) !== -1){
                    rows.push({
                        workspace_id: item.workspace_id,
                        date: item.date,
                        day_sheet_owner_id: item.doctor.id,
                        day_sheet_owner_type: item.day_sheet_owner_type,
                        clinic_id: item.clinic_id,
                    });
                }
            });
            return rows;
        },
        weekDays() {
            return [
                {
                    id: 2,
                    value: __('Пн'),
                },
                {
                    id: 3,
                    value: __('Вт'),
                },
                {
                    id: 4,
                    value: __('Ср'),
                },
                {
                    id: 5,
                    value: __('Чт'),
                },
                {
                    id: 6,
                    value: __('Пт'),
                },
                {
                    id: 7,
                    value: __('Сб'),
                },
                {
                    id: 1,
                    value: __('Вс'),
                },
            ];
        },
    }
}
</script>