import CONSTANTS from '@/constants';
import TreatmentCourseRepository from '@/repositories/treatment-course';
import EmployeeRepository from '@/repositories/employee';
import SearchPatient from '@/components/patients/search/Search.vue';
import StatusMixin from './status';
import TogglePatientFilter from '@/components/patients/search/ToggleFilter.vue';
import InformationSourceRepository from '@/repositories/patient/information-source';
import ProxyRepository from '@/repositories/proxy-repository';
import TreatmentCourse from '@/models/treatment-course';

export default {
    mixins: [
        StatusMixin,
    ],
    props: {
        model: {
            type: Object,
        },
        daySheetData: {
            type: Object,
        },
        cardList: {
            type: Array,
        },
        statuses: {
            type: Array,
            default: () => [],
        },
        surgerySpecialization: {
            type: Number,
            default: null,
        },
    },
    data() {
        return {
            sources: new ProxyRepository(({filters, sort, page, limit}) => {
                let repository = new InformationSourceRepository();
                return repository.fetchList({
                    ...filters,
                    clinic: this.model.clinic_id,
                }, sort, limit);
            }),
            appointments: [],
            locks: [],
            time_sheets: [],
            clinics: [],
            specializations: [],
            doctorList: [],
            patients: [],
            operators: new EmployeeRepository(),
            patientCourses: [],
            selectedDuration: '',
            minuteList: [],
            disabled: true,
            pickerOptions: {
                start: CONSTANTS.TIME_PICKER_OPTIONS.START,
                step: CONSTANTS.TIME_PICKER_OPTIONS.STEP,
                end: CONSTANTS.TIME_PICKER_OPTIONS.END
            },
        }
    },
    computed: {
        doctorLabel() {
            let first = this.daySheetData.doctor.appointment_duration;
            let repeated = this.daySheetData.doctor.appointment_duration_repeated;
            return __('Перв/Повт: {first}/{repeated}', {first, repeated});
        },
        appointmentData() {
            return {
                hasPrice: {
                    from: this.model.date,
                    to: this.model.date,
                    clinic: this.model.clinic_id,
                    set: [CONSTANTS.PRICE.SET_TYPE.BASE],
                },
                specialization: this.getDoctorSpecializations(),
                discountCard: this.discountCard,
                refreshDiscountType: this.refreshDiscountType,
                firstCalcDiscountCard: this.firstCalcDiscountCard,
                oldDiscountCard: this.oldDiscountCard,
                disabled: false,
                surgerySpecialization: this.surgerySpecialization,
                clinic: _.first(this.daySheetData.clinics),
            };
        },
        clearableCard() {
            if (this.$store.state.user.isOperator || this.$store.state.user.isReception) {
                return true;
            }
            return _.isFilled(this.model.card_specialization_id) &&
                   _.isFilled(this.model.specialization_id) &&
                   (this.model.card_specialization_id !== this.model.specialization_id);
        },
        getSourceFilters() {
            return {
                clinic: this.model.clinic_id,
            };
        },
    },
    methods: {
        makeMinutesList() {
            let doctor = this.daySheetData.doctor;
            let minMinutes = CONSTANTS.SCHEDULE_TIME_STEP;
            let maxMinutes = doctor.is_hospital_room 
                ? this.getTimeSheetEnd()
                : CONSTANTS.APPOINTMENT.TIME_MAX.ORDINARY;
            this.minuteList = [];

            do {
                this.minuteList.push(minMinutes);
                minMinutes += CONSTANTS.SCHEDULE_TIME_STEP;
            } while (minMinutes <= maxMinutes)
        },
        getLists() {
            this.getPatientCourses(this.model.patient_id);
        },
        getTimeSheetEnd() {
            let timeSheets = _.orderBy(this.daySheetData.time_sheets, 'end', 'desc');
            let maxEnd = this.$moment(this.model.date + ' ' + this.getTimeString(timeSheets[0].time_to));
            return maxEnd.diff(this.model.date + ' ' + this.getTimeString(this.model.start), 'minutes');
        },
        getPatientCourses(patient_id) {
            if (patient_id) {
                let treatmentCourse = new TreatmentCourseRepository();
                treatmentCourse.fetchList({patient: patient_id}).then((response) => {
                    let appointmentDate = this.$moment(this.model.date);
                    this.patientCourses = response.filter(item => {
                        if (_.isVoid(item.end) || item.id === this.model.treatment_course_id) {
                            return true;
                        }
                        return this.$moment(item.end).isSameOrAfter(appointmentDate);
                    });
                    this.setAppointmentTreatmentCourse();
                });
            }
        },
        setAppointmentTreatmentCourse() {
            if (_.isFilled(this.model.card_specialization_id)) {
                let course = null;

                if (this.model.isNew()) {
                    course = this.patientCourses.find(item => {
                        return item.card_specialization_id == this.model.card_specialization_id && _.isVoid(item.end);
                    });
                } else if (this.model.treatment_course_id) {
                    course = this.patientCourses.find(item => {
                        return item.id == this.model.treatment_course_id
                            && item.card_specialization_id == this.model.card_specialization_id;
                    });
                }

                if (course) {
                    this.model.treatment_course_id = course.id;
                } else {
                    this.model.treatment_course_id = null;
                }
            } else {
                this.model.treatment_course_id = null;
            }
        },
        makeOperatorsList(list) {
            this.operators = list;
            let loggedUser = this.$store.state.user;
            if(_.findIndex(this.operators, {id: loggedUser.id}) == -1){
                this.operators.push({id: loggedUser.id, value: loggedUser.login});
            }
            this.model.operator_id = loggedUser.id;
        },
        findPatient() {
            this.$modalComponent(SearchPatient, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    dialog.close();
                    this.onPatientSelected(patient);
                },
            }, {
                header: __('Фильтр поиска контактных лиц'),
                width: '1270px',
                headerAddon: {
                    component: TogglePatientFilter,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                    },
                },
            });
        },
        editPatient() {
            this.displayEditPatientForm(this.model.patient_id,
                (patient) => this.onPatientSelected(patient),
                (patient) => this.onPatientSelected(patient),
                {
                    afterFetch: (patient) => {
                        patient.appointmentClinic = this.model.clinic_id;
                    },
                }
            );
        },
        onPatientSelected(patient) {
            this.$parent.setPatient(patient);
            this.updatePatientListData(patient);
        },
        updatePatientListData(patient) {
            this.patients = [{ id: patient.id, value: patient.full_name }];
            this.daySheetData.patients = this.patients;
        },
        getDoctorSpecializations() {
            let list = [];

            if (this.model.doctor && this.model.doctor.specializations.length !== 0) {
                this.model.doctor.specializations.forEach((item) => list.push(item.id));
            }

            return list;
        },
        getTimeString(time) {
            if (time.length === 5) {
                time += ":00";
            }
            return time;
        },
    }
}
