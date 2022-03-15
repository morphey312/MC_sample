<template>
    <div class="sections-wrapper">
        <section class="grey-cap flex-content mt-0 pt-0">
            <patients-list
                :users="card.patients"
                :card="card"
                @patient-selection-changed="patientChanged"
            >
            </patients-list>
            <appointments-list
                class="mb-10"
                :appointments="appointments"
                :selected-patient-id="selectedPatientID"
                @appointment-selection-changed="appointmentChanged"
            >
            </appointments-list>
            <services-list
                :appointment-ids="appointmentIds"
            >
            </services-list>
            <div class="form-footer text-right">
                <el-button
                    :disabled="selectedPatientID === null"
                    @click="goToPatientCabinet">
                    {{ __('Переход в личный кабинет пациента') }}
                </el-button>
                <el-button
                    :disabled="selectedAppointment === null || selectedAppointment === []"
                    @click="edit">
                    {{ __('Изменить запись') }}
                </el-button>
                <el-button
                    @click="close">
                    {{ __('Отменить') }}
                </el-button>
            </div>
        </section>
    </div>
</template>

<script>
import ManageMixin from '@/mixins/manage';
import PatientsList from './UsersList.vue';
import AppointmentsList from './AppointmentsList.vue';
import ServicesList from './ServicesList.vue';
import AppointmentManager from '@/components/appointments/mixin/manager';
import Appointment from "@/models/appointment";

export default {
    mixins: [
        ManageMixin,
        AppointmentManager,
    ],
    components: {
        PatientsList,
        AppointmentsList,
        ServicesList
    },
    props: {
        card: Object
    },
    data() {
        return {
            users: null,
            model: null,
            loading: false,
            appointments: this.card.used_in_appointments,
            services: [],
            selectedAppointment: null,
            selectedPatientID: null,
            selectedAppointmentID: null,
        }
    },
    computed: {
        appointmentIds(){
            if(this.selectedAppointmentID){
                return [this.selectedAppointmentID];
            }

            if(this.selectedPatientID){
                return  this.appointments[this.selectedPatientID] ?
                    this.appointments[this.selectedPatientID].map((appointment) => appointment.id)
                    : []
            }

            let out = [];

            Object.keys(this.appointments).forEach(key => out = [...out, ...this.appointments[key]])

            return out.map((appointment) => appointment.id);
        }
    },
    methods: {
        edit() {
            let appointment = new Appointment({ id: this.selectedAppointment.id });
            appointment.fetch([
                'doctor',
                'clinic',
            ]).then(() => {
                this.makeDaySheetData(appointment, true).then(() => {
                    this.editAppointment((appointment) => {
                        this.daySheetData = {};
                    }, appointment);
                });
            })
        },
        close(){
            this.$emit('cancel');
        },
        patientChanged(patient){
            this.selectedPatientID = patient.length ? patient[0].patient_id : null;
            this.selectedAppointmentID = null;
            this.selectedAppointment = null;
        },
        appointmentChanged(appointment){
            this.selectedAppointment = appointment.length ? appointment[0] : null;
            this.selectedAppointmentID = appointment.length ? appointment[0].id : null;
        },
        goToPatientCabinet(){
            let routeData = this.$router.resolve({name: 'patient-cabinet-info', params: {patientId: this.selectedPatientID}});
            window.open(routeData.href, '_blank');
        }
    }
}
</script>
