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
            :doctor-label="doctorLabel"
            :selected-duration="selectedDuration"
            :minute-list="minuteList"
            :edit-blocked="!$can('appointments.edit-deleted')"
            @edit-patient="editPatient"
            @find-patient="findPatient"
            @services-loaded="servicesLoaded"
        />
        <div>
            <slot name="buttons"></slot>
        </div>
    </div>
</template>
<script>
import CONSTANTS from '@/constants';
import FormMixin from '@/components/appointments/mixin/form';
import AppointmentForm from './Form.vue';

export default {
    mixins: [
        FormMixin,
    ],
    components: {
        AppointmentForm,
    },
    beforeMount() {
        this.castToVariables();
        this.setDuration();
    },
    mounted() {
        this.getLists();
    },
    watch: {
        ['model.patient_id'](val) {
            this.getPatientCourses(val);
            this.castToVariables();
        },
    },
    methods: {
        setDuration() {
            this.selectedDuration = this.$moment(`${this.model.date} ${this.getTimeString(this.model.end)}`)
                                        .diff(`${this.model.date} ${this.getTimeString(this.model.start)}`, 'minutes');
        },
        castToVariables() {
            this.specializations = this.daySheetData.specializations;

            if(!this.model.specialization_id) {
                this.model.set('specialization_id', _.head(this.specializations).id);
            }
            
            this.clinics = this.daySheetData.clinics;
            this.patients = this.daySheetData.patients;
            this.doctorList = this.daySheetData.doctor_list;
            this.appointments = this.daySheetData.appointments;
            this.locks = this.daySheetData.locks;
            this.time_sheets = this.daySheetData.time_sheets;
        },
        servicesLoaded() {
            this.$emit('services-loaded');
        },
    }
};
</script>
