<template>
    <div class="appointment-block" v-loading="loading">
        <schedule-block
            :day-list="filteredDayList"
            :time-range="timeRange"
            :patient="patient"
            :cashier="cashier"
            :cashboxes="cashboxes"
            :readonly="readonly"
            :checkbox-cashboxes="checkboxCashboxes"
            @thumb-left="thumbLeft"
            @thumb-right="thumbRight"
            @switch-day="switchDate" />
    </div>
</template>

<script>
import DaySheet from '@/models/day-sheet';
import DaySheetRepository from '@/repositories/day-sheet';
import ScheduleBlock from './Block.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        ScheduleBlock,
    },
    props: {
        params: {
            type: [Object, Array],
        },
        timeRange: {
            type: [Object, Array],
            required: true
        },
        patient: {
            type: Object,
            default: () => ({}),
        },
        filters: {
            type: Object,
        },
        readonly: {
            type: Boolean,
            default: false,
        },
        cashier: Object,
        cashboxes: {
            type: Array,
            default: () => [],
        },
        checkboxCashboxes: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            repository: new DaySheetRepository(),
            dayList: [],
            filteredDayList: [],
            filterList: {
                clinics: [],
                specializations: [],
                doctors: [],
                statuses: [],
            },
        }
    },
    computed: {
        loading() {
            return this.dayList.length === 0;
        },
    },
    created() {
        this.listenRemoveDaysheetItem = ({params, daySheetId}) => {
            if (!_.isEmpty(params)) {
                let index = _.findIndex(this.dayList, {id: daySheetId});
                this.unlockUserLocks(this.dayList[index], daySheetId).then(() => {
                    this.dayList.splice(index, 1);
                    this.$emit('remove-param', params);
                });
            } else {
                this.$emit('remove-param', params);
            }
        }

        this.listenRemoveAllDaysheets = () => {
            this.unlockAllUserLocks().then(() => {
                this.dayList.splice(0);
                this.$emit('remove-all-params');
            });
        }

        this.listenAppointmentPatientUpdated = ({patient}) => {
            this.updateAppointmentsPatientData(patient);
        }
    },
    mounted() {
        this.$eventHub.$on('remove-daysheet-item', this.listenRemoveDaysheetItem);
        this.$eventHub.$on('remove-all-daysheets', this.listenRemoveAllDaysheets);
        this.$eventHub.$on('appointment-patient-updated', this.listenAppointmentPatientUpdated);
    },
    beforeDestroy() {
        this.$eventHub.$off('remove-daysheet-item', this.listenRemoveDaysheetItem);
        this.$eventHub.$off('remove-all-daysheets', this.listenRemoveAllDaysheets);
        this.$eventHub.$off('appointment-patient-updated', this.listenAppointmentPatientUpdated);
    },
    watch: {
        filters() {
           this.applyFilters();
        },
        patient: {
            handler: 'updateAppointmentsPatientData',
            deep: true
        },
    },
    methods: {
        unlockAllUserLocks() {
            let loggedUser = this.$store.state.user.employee_id;
            let daysWithUserLocks = this.dayList.filter(day => {
                let userLocks = day.locks.filter(lock => {
                    return lock.type === 'fixed' || lock.employee_id == loggedUser;
                });

                return userLocks.length !== 0;
            });
            daysWithUserLocks.forEach(day => {
                this.unlockUserLocks(day, day.id);
            });
            return Promise.resolve();
        },
        unlockUserLocks(day, daySheetId) {
            if (this.$can('appointments.create')) {
                let daySheet = new DaySheet({id: daySheetId});
                let locks = this.filterUserLocks(day, this.$store.state.user.employee_id);
                if (locks.length !== 0) {
                    return this.sendUnlocks(daySheet, locks);
                }
            }
            return Promise.resolve();
        },
        filterUserLocks(day, loggedEmployeeId) {
            return day.locks.filter(lock => {
                return lock.type !== 'fixed' && lock.employee_id === loggedEmployeeId;
            });
        },
        sendUnlocks(daySheet, locks) {
            let ids = locks.map(lock => lock.id);
            return daySheet.listUnlock(ids);
        },
        getSchedules() {
            return this.repository.getSchedules(this.params).then((response) => {
                if (response.length == 0) {
                    return this.$error(__('У выбранных врачей нет табеля на этот день'));
                }

                return this.renderGrid(response).then(() => {
                    this.dayList = this.sortDaySheets(response);
                    this.makeFilterValues();
                    this.applyFilters();
                    this.updateParams();
                    return Promise.resolve();
                });
            });
        },
        updateParams() {
            let params = [];

            this.dayList.forEach((day) => {
                params.push({
                    workspace_id: day.workspace_id,
                    date: day.date,
                    day_sheet_owner_id: day.doctor.id,
                    day_sheet_owner_type: day.day_sheet_owner_type,
                    doctor_id: day.doctor_id,
                    clinic_id: day.clinic_id,
                });
            });

            this.$emit('params-updated', params);
        },
        renderGrid(list) {
            let start = CONSTANTS.DAY_SHEET.GRID_PERIOD.START;
            let end = CONSTANTS.DAY_SHEET.GRID_PERIOD.END;

            list.forEach((day_sheet) => {
                day_sheet.time_sheets.forEach((time_sheet) => {
                    if (start > time_sheet.time_from) {
                        start = time_sheet.time_from;
                    }
                    if (end < time_sheet.time_to) {
                        end = time_sheet.time_to;
                    }
                });
            });
            this.$emit('grid-changed', {start, end});
            return Promise.resolve();
        },
        sortDaySheets(list) {
            //TODO: Turn on sort by item.doctor.specializations[0].order when they'll decide with priorities

            return _.sortBy(list, ['date', (item) => {
                return item.doctor.specializations[0].value;
            }]);
        },
        thumbLeft({index}) {
            let daySheet = this.filteredDayList[index];
            let thumbDay = this.$moment(daySheet.date).subtract(1, 'days').format('YYYY-MM-DD');
            let filters = this.getSwitchFilters(thumbDay, daySheet);
            this.updateColumnData(filters, daySheet, thumbDay, index);
        },
        thumbRight({index}) {
            let daySheet = this.filteredDayList[index];
            let thumbDay = this.$moment(daySheet.date).add(1, 'days').format('YYYY-MM-DD');
            let filters = this.getSwitchFilters(thumbDay, daySheet);
            this.updateColumnData(filters, daySheet, thumbDay, index);
        },
        switchDate({index, date}) {
            let daySheet = this.filteredDayList[index];
            let thumbDay = date;
            let filters = this.getSwitchFilters(thumbDay, daySheet);
            this.updateColumnData(filters, daySheet, thumbDay, index);
        },
        getSwitchFilters(date, daySheet) {
            return {
                day_sheet_owner_id: daySheet.doctor.id,
                day_sheet_owner_type: daySheet.day_sheet_owner_type,
                clinic_id: daySheet.clinic.id,
                date: date,
                workspace_id: daySheet.workspace_id,
            }
        },
        updateColumnData(filters, daySheet, date, index) {
            this.getSingleDaySheet(filters).then((data) => {
                let oldVal = {...daySheet};
                if (!_.isEmpty(data)) {
                    this.setColumnData(data, index);
                } else {
                    this.makeVacationDayData(daySheet, date);
                }
                this.$emit('param-updated', {oldVal, newVal: filters});
            });
        },
        getSingleDaySheet(filters) {
            return this.repository.fetchSingleDay(filters).then((response) => {
                return Promise.resolve(response);
            });
        },
        setColumnData(data, index) {
            this.dayList.splice(index, 1, data);
            this.renderGrid([...this.dayList])
            this.makeFilterValues();
            this.applyFilters();
        },
        makeVacationDayData(daySheet, date) {
            daySheet.id = null;
            daySheet.date = date;
            daySheet.locks = [];
            daySheet.appointments = [];
            daySheet.time_sheets = [];
        },
        makeFilterValues() {
            if (_.isEmpty(this.dayList)){
                return;
            }

            let clinics = [];
            let specializations = [];
            let doctors = [];
            let statuses = [];

            this.dayList.forEach((item) => {
                clinics.push({
                    id: item.clinic.id,
                    value: item.clinic.name
                });

                doctors.push({
                    id: item.doctor.id,
                    value: item.doctor.full_name
                });

                item.doctor.specializations.forEach((specialization) => {
                    specializations.push(specialization);
                });

                item.appointments.forEach((appointment) => {
                    if (_.isFilled(appointment.appointment_status_id)) {
                        statuses.push({
                            id: appointment.appointment_status_id,
                            value: appointment.status.name
                        });
                    }
                });
            });

            this.filterList.clinics = _.uniqBy(clinics, 'id');
            this.filterList.specializations = _.uniqBy(specializations, 'id');
            this.filterList.doctors = _.uniqBy(doctors, 'id');
            this.filterList.statuses = _.uniqBy(statuses, 'id');
            this.$emit('filter-list-changed', this.filterList);
        },
        applyFilters() {
            this.filteredDayList = this.dayList;

            if (_.isEmpty(this.filters)){
                this.$eventHub.$emit('highlight-appointments', false);
                return;
            }

            if (this.filters.clinic){
                this.filteredDayList = this.filteredDayList.filter((item) => {
                    return this.filters.clinic.indexOf(item.clinic.id) !== -1 ;
                });
            }

            if (this.filters.doctor){
                this.filteredDayList = _.filter(this.filteredDayList, (item) => {
                    return item.doctor.id == this.filters.doctor;
                });
            }

            if (this.filters.specialization){
                this.filteredDayList = _.filter(this.filteredDayList, (item) => {
                    let matchSpecialization = _.filter(item.doctor.specializations, {'id': this.filters.specialization});
                    return !_.isEmpty(matchSpecialization);
                });
            }

            if (this.filters.date_from){
                this.filteredDayList = _.filter(this.filteredDayList, (item) => {
                    return item.date >= this.filters.date_from;
                });
            }

            if (this.filters.date_to){
                this.filteredDayList = _.filter(this.filteredDayList, (item) => {
                    return item.date <= this.filters.date_to;
                });
            }

            this.$eventHub.$emit('highlight-appointments', this.filters.status ? this.filters.status : false);
        },
        updateSelectedPatientData() {
            return this.updateAppointmentsPatientData(this.patient);
        },
        updateAppointmentsPatientData(patient) {
            if(this.dayList.length == 0 || _.isEmpty(patient)) {
                return;
            }

            this.dayList.forEach((day) => {
                if(day.appointments.length > 0) {
                    day.appointments.forEach((appointment) => {
                        if (patient.id == appointment.patient.id) {
                            appointment.patient = patient;
                        }
                    });
                }
            });
        },
    },
}
</script>
