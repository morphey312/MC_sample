import DaySheetRepository from '@/repositories/day-sheet';
import DaySheet from '@/models/day-sheet';
import DaySheetMapTime from '@/mixins/day-sheet/map-time';
import FormEdit from '../modal/form/FormEdit.vue';
import FormActive from '../modal/form/FormActive.vue';
import FormInactive from '../modal/form/FormInactive.vue';
import FormHeaderButtons from '@/components/appointments/modal/form/HeaderButtons.vue';

export default {
    mixins: [
        DaySheetMapTime,
    ],
    data() {
        return {
            daySheetData: {},
            relatedSheets: [],
        }
    },
    created() {
        this.listenDaysheetLock = ({data}) => {
            if (data[this.daySheetData.id]) {
                this.daySheetData.locks = data[this.daySheetData.id];
                this.initMomentValues(this.daySheetData.locks, this.daySheetData.date);
            }
        }
    },
    mounted() {
        this.$eventHub.$on('broadcast.day_sheet_lock', this.listenDaysheetLock);
    },
    beforeDestroy() {
        this.$eventHub.$off('broadcast.day_sheet_lock', this.listenDaysheetLock);
    },
    methods: {
        makeDaySheetData(appointment, compact = false) {
            this.relatedSheets = [];
            let day_sheet = new DaySheetRepository();

            let filters = {
                date: appointment.date,
                day_sheet_owner_id: appointment.doctor_id,
                day_sheet_owner_type: appointment.doctor_type,
                clinic_id: appointment.clinic_id,
                workspace_id: appointment.workspace_id,
            };
            return day_sheet.fetchSingleDay(filters, compact).then((day_sheet) => {
                if(_.isEmpty(day_sheet)) {
                    return Promise.resolve();
                }

                return this.getRelatedSheets(day_sheet).then(() => {
                    this.castDaySheetData(day_sheet, appointment);
                    return Promise.resolve();
                });
            });
        },
        getRelatedSheets(day) {
            let model = new DaySheet({id: day.id});

            return model.getRelatedSheets().then((response) => {
                this.relatedSheets = this.mapAppointmentTimes(response);
                return Promise.resolve();
            });
        },
        getAllDoctorAppointments(day) {
            let appointments = [...day.appointments];

            if(this.relatedSheets.length != 0) {
                this.relatedSheets.forEach((sheet) => {
                    if(sheet.appointments.length != 0) {
                        appointments = [...appointments, ...sheet.appointments];
                    }
                });
            }

            return appointments;
        },
        getFormSpecializations(day) {
            let list = [];

            day.time_sheets.forEach((item) => {
                item.specializations.forEach((id) => {
                    let value = item.specialization_data[id].name;
                    if (_.findIndex(list, {id: id, value: value}) === -1) {
                        let serviceGroup = item.specialization_data[id].service_group;
                        list.push({
                            id: id,
                            value: value,
                            service_group: serviceGroup,
                        });
                    }
                });
            });

            return list;
        },
        castDaySheetData(day, appointment) {
            this.mapAppointmentsData(day);
            this.initMomentValues(day.locks, day.date);
            this.daySheetData = {
                id: day.id,
                date: day.date,
                appointment: appointment,
                specializations: this.getFormSpecializations(day),
                appointment_duration: this.getAppointmentDuration(appointment),
                doctor: day.doctor,
                appointments: this.getAllDoctorAppointments(day),
                locks: day.locks,
                time_sheets: day.time_sheets,
                clinics: [
                    {
                        id: appointment.clinic_id,
                        value: appointment.clinic_name,
                        not_round_cost: (day.clinic) ? day.clinic.not_round_cost : false,
                    }
                ],
                patients: [
                    {
                        id: appointment.patient.id,
                        value: appointment.patient.full_name,
                    }
                ],
                doctor_list: [
                    {
                        id: day.doctor.id,
                        value: day.doctor.full_name,
                    }
                ],
            };
        },
        editAppointment(afterUpdate, item) {
            this.$modalComponent(FormEdit, {
                daySheetData: this.daySheetData,
                form: (item.is_deleted ? FormInactive : FormActive),
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                updated: (dialog, appointment) => {
                    dialog.close();
                    if(afterUpdate && _.isFunction(afterUpdate)) {
                        afterUpdate(appointment);
                    }
                },
                patientSelected: (dialog, patient) => {
                    dialog.getTopHeaderAddon().setPatient(patient);
                },
            },
            {
                header: __('Запись пациента на прием'),
                width: '1265px',
                headerAddon: {
                    component: FormHeaderButtons,
                    props: {
                        appointment: this.daySheetData.appointment,
                    },
                    eventListeners: {
                        discountSelected: (dialog, [card, refreshDiscountType = 0]) => {
                            dialog.getTopComponent().setDiscountCard(card, refreshDiscountType);
                        },
                        insuranceSelected: (dialog, policy) => {
                            dialog.getTopComponent().setInsurancePolicy(policy);
                        },
                        legalEntitySelected: (dialog, legalEntity) => {
                            dialog.getTopComponent().setLegalEntity(legalEntity);
                        },
                        ambulanceCall: (dialog, ambulanceCall) => {
                            dialog.getTopComponent().setAmbulanceCall(ambulanceCall);
                        },
                    }
                },
            });
        },
    }
}
