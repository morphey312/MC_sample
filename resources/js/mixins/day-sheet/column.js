import CONSTANTS from '@/constants';

export default {
	methods: {
        countCells(appointment) {
            let duration = this.getAppointmentDuration(appointment);
            let totalCells = 1;

            if(duration > CONSTANTS.SCHEDULE_TIME_STEP) {
                totalCells = duration / CONSTANTS.SCHEDULE_TIME_STEP;
            }

            if(_.isFunction(appointment.set)) {
                appointment.set('duration', duration);
                appointment.set('totalCells', totalCells);
            } else {
                appointment.duration = duration;
                appointment.totalCells = totalCells;
            }
        },
        rejectOldAppointment(id) {
            let list = [...this.appointments];
            this.appointments = list.filter(item => item.id != id);
        },
        setNewAppointmentTime(appointment, period) {
            appointment.start = period.newStart.format('HH:mm:ss');
            appointment.end = period.newEnd.format('HH:mm:ss');
        },
        isInTimeSheets(period) {
            let validEnd = false;

            _.each(this.columnDay.time_sheets, (timeSheet) => {
                let from = `${this.day.date} ${timeSheet.time_from}`;
                let to   = `${this.day.date} ${timeSheet.time_to}`;

                if(period.newEnd.isBetween(from, to) || period.newEnd.isSame(to)){
                    validEnd = true;
                }
            });

            return validEnd;
        },
        isValidTimeDuration(appointment, dropTime, period) {
            let newEnd = period.newEnd.format('HH:mm');
            let appointments = this.getAllDoctorAppointments();

            let crossMatch = _.find(appointments, (item) => {
                return  (item.id != appointment.id) &&
                        (item.momented.scheduleStart > dropTime) &&
                        (item.momented.scheduleStart < newEnd)
            });

            return _.isNil(crossMatch);
        },
        getAllDoctorAppointments() {
            let appointments = [...this.appointments];

            if(this.relatedSheets.length != 0) {
                _.each(this.relatedSheets, (sheet) => {
                    if(sheet.appointments.length != 0) {
                        appointments = [...appointments, ...sheet.appointments];
                    }
                });
            }

            return appointments;
        },
        makeNewAppointmentPeriod(appointment, dropTime) {
            const newStart = this.$moment(`${this.columnDay.date} ${dropTime}:00`);
            const newEnd = this.$moment(`${this.columnDay.date} ${dropTime}:00`).add(appointment.duration, 'minutes');

            return {newStart, newEnd};
        },
        getLockPeriodIndex(unlockTime) {
            return _.findIndex(this.columnDay.locks, (period) => {
                return (period.start == unlockTime) ||
                       (period.start < unlockTime && period.end > unlockTime);
            });
        },
        getFormSpecializations(time = null) {
            let list = [];
            let appointmentTime = time ? this.$moment(`${this.columnDay.date} ${time}`) : null;

            this.columnDay.time_sheets.forEach((item) => {
                if (time) {
                    if (appointmentTime.isBetween(item.momented.start, item.momented.end, null, '[)')) {
                        list = this.extractSpecializations(list, item);
                        return false;
                    }
                } else {
                    list.concat(this.extractSpecializations(list, item));
                }
            });

            return list;
        },
        extractSpecializations(list, item){
            item.specializations.forEach((id) => {
                let value = item.specialization_data[id];
                if (_.findIndex(list, {id: id, value: value.name}) === -1) {
                    list.push({
                        id: id,
                        value: value.name,
                        service_group: value.service_group,
                    });
                }
            });
            return list;
        },
        makeDataForCreate(time) {
            this.daySheetData = {
                id: this.day.id,
                specializations: this.getFormSpecializations(time),
                appointment_duration: this.day.doctor.appointment_duration,
                doctor: this.day.doctor,
                appointments: this.getAllDoctorAppointments(),
                locks: this.day.locks,
                time_sheets: this.columnDay.time_sheets,
                appointment: {
                    date: this.day.date,
                    start: time,
                    clinic_id: this.day.clinic.id,
                    specialization_id: this.day.specialization_id,
                    patient_id: _.isEmpty(this.patient) ? null : this.patient.id,
                    appointment_status_id: null,
                    is_first: 1,
                    operator_id: this.$store.state.user.employee_id,
                    doctor_id: this.day.doctor.id,
                    workspace_doctor_id: this.day.doctor_id,
                    doctor_type: this.day.day_sheet_owner_type,
                    source: null,
                    treatment_course_id: null,
                    patient: _.isEmpty(this.patient) ? {} : this.patient,
                    workspace_id: this.day.workspace_id,
                    insurance_policy_id: null,
                },
                clinics: [{
                    id: this.day.clinic.id,
                    value: this.day.clinic.name,
                    not_round_cost: this.day.clinic.not_round_cost,
                }],
                patients: _.isEmpty(this.patient) ? [] : [{
                            id: this.patient.id,
                            value: this.patient.full_name,
                        }],
                doctor_list: [{
                    id: this.day.doctor.id,
                    value: this.day.doctor.full_name,
                }],
            };
        },
        makeDataForEdit(appointment) {
            this.daySheetData = {
                id: this.day.id,
                appointment: appointment,
                specializations: this.getFormSpecializations(appointment.start),
                appointment_duration: this.getAppointmentDuration(appointment),
                doctor: this.day.doctor,
                appointments: this.getAllDoctorAppointments(),
                locks: this.day.locks,
                time_sheets: this.columnDay.time_sheets,
                clinics: [{
                    id: this.day.clinic.id,
                    value: this.day.clinic.name,
                    not_round_cost: this.day.clinic.not_round_cost,
                }],
                patients: [{
                            id: appointment.patient.id,
                            value: appointment.patient.full_name,
                        }],
                doctor_list: [{
                    id: this.day.doctor.id,
                    value: this.day.doctor.full_name,
                }],
            };
        },
        findNextAppointment(time) {
            let appointments = this.getAllDoctorAppointments();
            return _.find(appointments, (appointment) => {
                return appointment.start > `${time}:00`;
            });
        },
        findCrossBlockedPeriod(startTime, endTime) {
            return _.find(this.columnDay.locks, (period) => {
                return period.momented.start > startTime && period.momented.start < endTime;
            });
        },
        isLocked(time) {
            let momentedTime = this.$moment(`${this.day.date} ${time}:00`);
            let inPeriod = _.find(this.columnDay.locks, (item) => {
                return momentedTime.isSame(item.momented.start) ||
                       momentedTime.isBetween(item.momented.start, item.momented.end)
            });

            return inPeriod;
        },
	}
}
