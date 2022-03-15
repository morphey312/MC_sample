<template>
    <div>
        <appointment-form
            :model="model"
            :sources="sources"
            :patients="patients"
            :card-list="cardList"
            :clinics="clinics"
            :specializations="specializations"
            :doctor-list="doctorList"
            :operators="operators"
            :patient-courses="patientCourses"
            :statuses="statuses"
            :picker-options="pickerOptions"
            :clearable-card="clearableCard"
            :appointment-data="appointmentData"
            :operator-suggest-from="operatorSuggestFrom"
            :doctor-label="doctorLabel"
            :selected-duration="selectedDuration"
            :minute-list="minuteList"
            :enquiry="enquiry"
            :insurance-policy="insurancePolicy"
            @edit-patient="editPatient"
            @find-patient="findPatient"
            @duration-changed="durationChanged"
            @assigned-add="assignedAdd"
            @services-loaded="servicesLoaded"
        />
        <div>
            <slot name="buttons"></slot>
        </div>
    </div>
</template>
<script>
import CONSTANTS from '@/constants';
import MESSAGES from '@/messages';
import FormMixin from '@/components/appointments/mixin/form';
import DaySheetMapTime from '@/mixins/day-sheet/map-time';
import AppointmentForm from './Form.vue';
import InformationSourceRepository from '@/repositories/patient/information-source';

export default {
    components: {
        AppointmentForm,
    },
    mixins: [
        FormMixin,
        DaySheetMapTime,
    ],
    props: {
        operatorSuggestFrom: Object,
        oldDiscountCard: Object,
        firstCalcDiscountCard: Object,
        refreshDiscountType: Number,
        discountCard: Object,
        enquiry: Object,
        insurancePolicy: Object,
    },
    data(){
        return {
            isSuggestedSource: false,
            isRecommendation: false,
        }
    },
    watch: {
        ['model.patient_id'](val) {
            this.getPatientCourses(val);
            this.castToVariables(false);
            this.checkRedirects();
        },
        ['model.start'](val, prevVal) {
            return this.$nextTick(() => {
                this.verifyStartByTimeSheet(val, prevVal);
                this.verifyStartByLock(val, prevVal);
                this.verifyStartByAppointment(val, prevVal);
                this.verifyDuration(this.selectedDuration);
            });
        },
        ['model.is_first'](val) {
            if (val === CONSTANTS.APPOINTMENT.TYPES.FIRST) {
                this.checkRedirects();
            } else if (this.isSuggestedSource && this.isRecommendation) {
                this.model.source_id = null;
            }

            let duration;

            if (CONSTANTS.APPOINTMENT.TYPES.REPEATED == val) {
                duration = this.daySheetData.doctor.appointment_duration_repeated;
            }

            if (!this.model.isNew()) {
                if (!duration || duration < this.selectedDuration) {
                    return;
                }
            }

            this.initiateDuration(duration);
        },
        ['model.card_specialization_id'](val, oldVal) {
            if (this.model.id === null && oldVal !== null && val !== oldVal) {
                this.clearSourcesAndServices();
            } else if (oldVal !== null && val !== oldVal) {
                this.$warning(__('Источник информации не был изменен'));
            }
            this.setAppointmentTreatmentCourse();
            this.checkRedirects();
        },
        ['model.source_id']() {
            this.isSuggestedSource = false;
            this.isRecommendation = false;
        },

    },
    created() {
        this.listenDaysheetLock = ({data}) => {
            if (data[this.daySheetData.id]) {
                this.locks = data[this.daySheetData.id];
                this.initMomentValues(this.locks, this.daySheetData.date);
            }
        }
    },
    beforeDestroy() {
        this.$eventHub.$off('broadcast.day_sheet_lock', this.listenDaysheetLock);
    },
    beforeMount() {
        this.castToVariables();
    },
    mounted() {
        this.$eventHub.$on('broadcast.day_sheet_lock', this.listenDaysheetLock);
        this.getLists();
    },
    methods: {
        clearSourcesAndServices() {
            this.model.source_id = null;
            this.model.services = [];
            this.model.patient.assigned_consultations = [];
        },
        durationChanged(val) {
            this.selectedDuration = val;
            this.verifyDuration(val);
        },
        castToVariables(saveDuration = true) {
            this.specializations = this.daySheetData.specializations;

            if (!this.model.specialization_id) {
                this.model.set('specialization_id', _.head(this.specializations).id);
            }

            this.clinics = this.daySheetData.clinics;
            this.patients = this.daySheetData.patients;
            this.doctorList = this.daySheetData.doctor_list;
            this.appointments = _.sortBy(this.daySheetData.appointments, 'start');
            this.locks = this.daySheetData.locks;
            this.time_sheets = this.daySheetData.time_sheets;

            if (saveDuration) {
                this.initiateDuration();
                this.makeMinutesList();
            }
        },
        initiateDuration(durationVal) {
            let duration = durationVal || this.daySheetData.appointment_duration;
            let appointment_end = this.getAppointmentEnd(duration);
            let doctorOutOfTime = this.findTimeSheets(appointment_end);
            let formattedEnd = appointment_end.format('HH:mm') ;

            if (!_.isNil(doctorOutOfTime)) {
                return this.setDurationByTimeSheet(duration, doctorOutOfTime);
            }

            let lockCross = this.findLockCross(formattedEnd);

            if (!_.isNil(lockCross)){
                return this.setDurationByLock(duration, lockCross);
            }

            let crossMatch = this.findAppointmentCross(appointment_end);

            return this.setDurationByAppointment(duration, crossMatch);
        },
        verifyStartByAppointment(startVal, prevStartVal) {
            let crossMatch;

            if (this.model.id) {
                crossMatch = this.appointments.find((item) => {
                    return (item.id != this.model.id) &&
                           (item.momented.scheduleStart <= startVal) &&
                           (item.momented.scheduleEnd > startVal)
                });
            } else {
                crossMatch = this.appointments.find((item) => {
                    return (item.momented.scheduleStart <= startVal) &&
                           (item.momented.scheduleEnd > startVal)
                });
            }

            if (!_.isNil(crossMatch)) {
                this.$error(MESSAGES.ERROR.APPOINTMENT_CROSS_TIME);
                this.model.start = prevStartVal;
            }
        },
        verifyStartByLock(startVal, prevStartVal) {
            let crossMatch = this.locks.find((item) => {
                return (item.start < startVal) &&
                       (item.end > startVal) &&
                       (item.type === 'fixed' ? true : this.$store.state.user.employee_id != item.employee_id)
            });

            if (!_.isEmpty(crossMatch)) {
                this.$error(MESSAGES.ERROR.LOCK_CROSS_TIME);
                this.model.start = prevStartVal;
            }
        },
        verifyStartByTimeSheet(startVal, prevStartVal) {
            let newStart = this.$moment(`${this.model.date} ${this.getTimeString(startVal)}`);

            let crossMatch = this.time_sheets.find((timeSheet) => {
                let from = `${this.model.date} ${timeSheet.time_from}`;
                let to  = `${this.model.date} ${timeSheet.time_to}`;

                return newStart.isBetween(from, to) || newStart.isSame(from);
            });

            if (_.isNil(crossMatch)) {
                this.$error(MESSAGES.ERROR.DOCTOR_OUT_OF_TIME);
                this.model.start = prevStartVal;
            }
        },
        verifyDuration(duration) {
            let appointment_end = this.getAppointmentEnd(duration);
            let doctorOutOfTime = this.findTimeSheets(appointment_end);
            let formattedEnd = appointment_end.format('HH:mm');

            if (!_.isNil(doctorOutOfTime)) {
                return this.setDurationByTimeSheet(duration, doctorOutOfTime, true);
            }

            let lockCross = this.findLockCross(formattedEnd);

            if (!_.isEmpty(lockCross)) {
                return this.setDurationByLock(duration, lockCross, true);
            }

            let crossMatch = this.findAppointmentCross(appointment_end);
            let errorMessage = MESSAGES.ERROR.APPOINTMENT_CROSS_TIME;
            return this.setDurationByAppointment(duration, crossMatch, errorMessage);
        },
        setDurationByAppointment(duration, crossedDuration = {}, errorMessage = null) {
            let momentedStart = this.$moment(`${this.model.date} ${this.getTimeString(this.model.start)}`);

            if (_.isEmpty(crossedDuration)) {
                this.model.end = momentedStart.add(duration, 'minutes').format('HH:mm');
                this.selectedDuration = duration;
                return;
            }

            if (errorMessage) {
                this.$error(errorMessage);
            }

            let endToNext = this.$moment(`${this.model.date} ${this.getTimeString(crossedDuration.momented.scheduleStart)}`);
            this.model.end = endToNext.format('HH:mm');
            this.selectedDuration = endToNext.diff(momentedStart, 'minutes');
            return;
        },
        setDurationByLock(duration, crossedDuration, showMessage = false) {
            if (showMessage){
                this.$error(MESSAGES.ERROR.LOCK_CROSS_TIME);
            }

            let momentedStart = this.$moment(`${this.model.date} ${this.getTimeString(this.model.start)}`);
            let endToLock = crossedDuration.momented.start;
            this.model.set('end', crossedDuration.start);
            this.selectedDuration = endToLock.diff(momentedStart, 'minutes');
            return;
        },
        setDurationByTimeSheet(duration, crossedDuration, showMessage = false) {
            if (showMessage){
                this.$error(MESSAGES.ERROR.DOCTOR_OUT_OF_TIME);
            }

            let momentedStart = this.$moment(`${this.model.date} ${this.getTimeString(this.model.start)}`);
            let endToLock = this.$moment(`${this.model.date} ${crossedDuration.time_to}`) ;
            this.model.set('end', crossedDuration.time_to);
            this.selectedDuration = endToLock.diff(momentedStart, 'minutes');
            return;
        },
        findAppointmentCross(appointment_end) {
            let momentedStart = this.$moment(`${this.model.date} ${this.getTimeString(this.model.start)}`);

            if (this.model.id) {
                return this.appointments.find((item) => {
                    return (item.id != this.model.id) &&
                           (item.momented.start > momentedStart) &&
                           (item.momented.start < appointment_end)
                });
            }

            return this.appointments.find((item) => {
                return (item.momented.start > momentedStart) &&
                       (item.momented.start < appointment_end)
            });
        },
        findLockCross(appointment_end) {
            let employee_id = this.$store.state.user.employee_id;

            return this.locks.find((item) => {
                return (item.start < appointment_end) &&
                           (item.end > appointment_end) &&
                           (item.type === 'fixed' ? true : employee_id != item.employee_id)
            });
        },
        findTimeSheets(appointment_end) {
            let newStart = this.$moment(`${this.model.date} ${this.getTimeString(this.model.start)}`);

            if (!newStart.isSame(appointment_end, 'day')) {
                return null;
            }

            return this.time_sheets.find((timeSheet) => {
                let from = `${this.model.date} ${timeSheet.time_from}`;
                let to  = `${this.model.date} ${timeSheet.time_to}`;

                // Moment dates cheatsheet
                // A [ indicates inclusion of a value.
                // A ( indicates exclusion. If the inclusivity parameter is used, both indicators must be passed.
                return newStart.isBetween(from, to, null, '[)') &&
                    this.$moment(to) < appointment_end;
            });
        },
        getAppointmentEnd(duration) {
            return this.$moment(`${this.model.date} ${this.getTimeString(this.model.start)}`)
                .add(duration, 'minutes');
        },
        assignedAdd(assigned) {
            let sourceEmployees = [];

            if (assigned.services !== undefined) {
                sourceEmployees = assigned.services
                    .filter((service) => !service.is_no_auto_recommend_source)
                    .map((service) => service.assigner_id);
            } else if (assigned.analyses !== undefined) {
                sourceEmployees = assigned.analyses
                    .filter((analysis) => analysis.clinic_id !== this.model.clinic_id)
                    .map((analysis) => analysis.assigner_id);
            }

            if (sourceEmployees.length !== 0) {
                this.searchSources(sourceEmployees, false);
            }
        },
        checkRedirects() {
            this.$nextTick(() => {
                if (_.isVoid(this.model.source_id) &&
                    _.isFilled(this.model.patient) &&
                    _.isFilled(this.model.card_specialization_id))
                {
                    let thisDate = this.$moment(this.model.date + ' ' + this.model.start);
                    let consultations = this.model.patient.assigned_consultations;
                    let sourceEmployees = [];
                    let specialization = this.model.card_specialization_id;

                    if (consultations) {
                        consultations.forEach((cons) => {
                            if (cons.specialization_id === specialization && thisDate.isAfter(cons.date)) {
                                sourceEmployees.push(cons.doctor_id);
                            }
                        });
                    }

                    if (sourceEmployees.length !== 0) {
                        this.searchSources(sourceEmployees, true);
                    }
                }
            });
        },
        searchSources(sourceEmployees, isRecommendation) {
            let repository = new InformationSourceRepository();
            repository.fetchList(_.onlyFilled({
                clinic: this.model.clinic_id,
                employee: sourceEmployees,
            }), null, 1).then((response) => {
                if (response.length && (this.model.is_first === CONSTANTS.APPOINTMENT.TYPES.FIRST || !isRecommendation)) {
                    this.model.source_id = response[0].id;
                    this.$nextTick(() => {
                        this.isSuggestedSource = true;
                        this.isRecommendation = isRecommendation;
                    });
                }
            });
        },
        servicesLoaded() {
            this.$emit('services-loaded');
        },
    }
};
</script>
