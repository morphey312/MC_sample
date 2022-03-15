import DaySheet from '@/models/day-sheet';
import TimeSheet from '@/models/day-sheet/time-sheet';

export default {
    props: {
        day_sheet_owner_id: {
            type: [Number, String],
        },
        day_sheet_owner_type: {
            type: String,
        },
        clinic_id: {
            type: [Number, String],
        },
        dateList: {
            type: [String, Array],
        },
    },
    data() {
        return {
            model: new DaySheet(),
            loading: true,
            dates: [],
            specialization_list: [],
            filters: {},
            appointments: [],
            clinic_daysheets: [],
            workspace_list: [],
            errorAppointmentMessage: __('Нельзя изменить табель. Записи пациентов выходят за пределы рабочего времени'),
            errorEmptyTimesheetsMessage: __('Укажите период приема'),
            day_sheets: [],
        }
    },
    beforeMount(){
        this.makeDateList();
        this.castRouteToModelValues();
        this.getDaySheetData();
    },
    computed: {
        notifications() {
            let clinicData = {};

            if(this.clinic_daysheets.length > 0){
                _.each(this.clinic_daysheets, (item) => {
                    if(!clinicData[item.clinic.id]) {
                        clinicData[item.clinic.id] = {
                            name:item.clinic.name,
                            sheets: {},
                            visible: false,
                        };
                    }

                    clinicData[item.clinic.id].sheets[item.date] = item.time_sheets;
                });
            }

            return clinicData;
        },
        emptyAppointments() {
            return this.appointments.length === 0;
        },
        emptyTimeSheets() {
            return this.model.time_sheets.length === 0;
        },
    },
    methods: {
        validateTime({item, oldVal, field}, newVal) {
            let fullTime = `${newVal}:00`;

            if (this.appointments.length > 0 && this.timeOutOfAppointments(fullTime)){
                this.$error(this.errorAppointmentMessage);
                this.$nextTick(() => item.set(field, oldVal));
                return;
            }
        },
        makeDateList() {
            if (typeof this.dateList == 'string'){
                this.dates.push(this.dateList);
            } else {
                this.dates = this.dateList;
            }
            this.dates.sort();
        },
        setModelData(data) {
            let time_sheets = [];

            if (data) {
                this.model.set('id', data.id);
                this.model.set('doctor_id', data.doctor_id);
                time_sheets = data.time_sheets.map((item) => {
                    item = this.model.castToInstance(TimeSheet, item);
                    this.castSpecializationData(item);
                    return item;
                });
            } else {
                let item = new TimeSheet();
                this.castSpecializationData(item);
                time_sheets.push(item);
            }
            this.model.time_sheets = time_sheets;
        },
        manageDaySheets(data) {
            let dayData = _.find(data, (item) => {
                return item.date == this.dates[0] && item.clinic.id == this.model.clinic_id;
            });

            this.setModelData(dayData);

            let otherClinicsSheets = _.filter(data, (item) => {
                return item.clinic.id != this.model.clinic_id;
            });

            this.clinic_daysheets = otherClinicsSheets;
        },
        setAppointments(data) {
            let appointments = [];

            data.forEach((item) => {
                if (item.clinic_id == this.model.clinic_id && item.appointments.length != 0) {
                    appointments = [...appointments, ...item.appointments];
                }
            });
            this.appointments = appointments;
        },
        cancel() {
            this.$emit('cancel');
        },
        update() {
            this.$clearErrors();

            if (this.model.time_sheets.length === 0) {
                return this.$error(this.errorEmptyTimesheetsMessage);
            }

            if (this.isInvalidAppointmentTimeSheets()) {
                return this.$error(this.errorAppointmentMessage);
            }
            return this.saveModel();
        },
        saveModel() {
            this.loading = true;
            this.model.save().then((response) => {
                this.loading = false;
                this.$info(__('Табель успешно обновлен'));
                this.$emit('updated');
            }).catch((e) => {
                this.loading = false;
                this.$displayErrors(e);
            });
        },
        deleteModel() {
            this.$clearErrors();
            if (this.appointments.length != 0) {
                return this.$error(__('Табель нельзя удалить, есть записи пациентов на прием в этот день'));
            }
            if (this.day_sheets.length > 1) {
                return this.deleteModelList();
            }
            return this.deleteSingleModel();
        },
        deleteModelList() {
            let list = this.day_sheets
                .filter(day => day.clinic_id == this.model.clinic_id)
                .map(day => day.id);
            this.loading = true;
            this.model.deleteList(list).then(() => {
                this.loading = false;
                this.$info(__('Список успешно удален'));
                this.$emit('updated');
            }).catch((e) => {
                this.loading = false;
                this.$displayErrors(e);
            });
        },
        deleteSingleModel() {
            this.loading = true;
            this.model.delete().then(() => {
                this.loading = false;
                this.$info(__('Табель успешно удален'));
                this.$emit('updated');
            }).catch((e) => {
                this.loading = false;
                this.$displayErrors(e);
            });
        },
        addTimeSheet() {
            this.model.addTimeSheet(this.specialization_list);
        },
        removeTimeSheet(index) {
            if(this.isInvalidRemove(index)) {
                return this.$error(this.errorAppointmentMessage);
            }
            return this.model.removeTimeSheet(index);
        },
        timeOutOfAppointments(fullTime) {
            let appointment = _.find(this.appointments, (item) => {
                return item.start < fullTime && item.end > fullTime;
            });
            return !_.isNil(appointment);
        },
        isInvalidAppointmentTimeSheets() {
            if (this.emptyAppointments) {
                return false;
            }

            let matched = 0;

            this.appointments.forEach((appointment) => {
                let appointment_start = this.momentedTime(appointment.start.substring(0, 5));
                let appointment_end = this.momentedTime(appointment.end.substring(0, 5));

                let outOfPeriod = _.find(this.model.time_sheets, (time_sheet) => {
                    let time_start = this.momentedTime(time_sheet.time_from);
                    let time_end = this.momentedTime(time_sheet.time_to);

                    return (appointment_start.isBetween(time_start, time_end) && appointment_end.isBetween(time_start, time_end))
                        || (appointment_start.isSame(time_start) && appointment_end.isSame(time_end))
                        || (appointment_start.isSame(time_start) && appointment_end.isBetween(time_start, time_end))
                        || (appointment_start.isBetween(time_start, time_end) && appointment_end.isSame(time_end));
                });

                if (_.isNil(outOfPeriod)) {
                    matched++;
                }
            });
            return matched != 0;
        },
        isInvalidRemove(index) {
            if(this.emptyAppointments) {
                return false;
            }

            let time_sheet = this.model.time_sheets[index];
            let time_start = this.momentedTime(time_sheet.time_from);
            let time_end = this.momentedTime(time_sheet.time_to);

            let cross_match = _.find(this.appointments, (appointment) => {
                let appointment_start = this.momentedTime(appointment.start.substring(0, 5));
                let appointment_end = this.momentedTime(appointment.end.substring(0, 5));

                return appointment_start.isBetween(time_start, time_end) ||
                       appointment_end.isBetween(time_start, time_end) ||
                       appointment_start.isSame(time_start) ||
                       appointment_end.isSame(time_end);
            });

            return !_.isNil(cross_match);
        },
        momentedTime(time, date) {
            if (date == undefined) {
                date = this.dates[0];
            }
            return this.$moment(`${date} ${time}:00`);
        },
        castRouteToModelValues() {
            this.model.set({
                dates: this.dates,
                day_sheet_owner_id: this.day_sheet_owner_id,
                day_sheet_owner_type: this.day_sheet_owner_type,
                clinic_id: this.clinic_id,
            });
        },
        minTime(item, index) {
            let prevItem = this.model.time_sheets[index - 1];

            if(prevItem) {
                let nextStart = this.$moment(`${prevItem.time_to}:00`, 'HH:mm:ss').subtract(1, 'minutes');
                return nextStart.format('HH:mm:ss').substring(0, 5);
            }

            return '';
        },
        findMatchTimeSheet(item) {
            return  _.find(this.model.time_sheets, (time_sheet) => {
                        let time_start = this.momentedTime(time_sheet.time_from);
                        let time_end = this.momentedTime(time_sheet.time_to);
                        let appointment_start = this.momentedTime(item.start.substring(0, 5));
                        let appointment_end = this.momentedTime(item.end.substring(0, 5));

                        return  appointment_start.isBetween(time_start, time_end) ||
                                appointment_end.isBetween(time_start, time_end) ||
                                appointment_start.isSame(time_start) ||
                                appointment_end.isSame(time_end);
                    });
        },
        appointmentOutOfSpecializations(item, time_sheet) {
            return time_sheet.specializations.indexOf(item.specialization_id) === -1
        },
        getAppointmentPeriod(appointment) {
            return `${appointment.start.substring(0, 5)} - ${appointment.end.substring(0, 5)}`;
        },
        getSpecializationError(appointment) {
            let specialization = _.find(this.specialization_list, {id: appointment.specialization_id});
            return __('По специализации {specialization} есть запись на прием: {period}', {specialization: specialization.name, period: this.getAppointmentPeriod(appointment)});
        },
    },
}
