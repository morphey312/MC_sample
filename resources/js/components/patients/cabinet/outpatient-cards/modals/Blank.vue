<template>
    <div v-loading="blank === null || !blank.ready || saving" >
        <section 
            ref="docContainer"
            id="blank-container"
            class="light-grey pdf-container">
        </section>
    </div>
</template>

<script>
import PdfBlank from '@/services/pdf-blank';
import printer from '@/services/print';
import PatientDocument from '@/models/patient-document';
import fileLoader from '@/services/file-loader';

export default {
    props: {
        patient: Object,
        file: Object,
        appointment: Object,
        signalRecord: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            blank: null,
            saving: false,
            blankDefaults: {
                clinic_requisites: this.appointment.clinic_requisites.official_fullname + ', ' + this.appointment.clinic_requisites.official_address,
                clinic_egrpo: this.appointment.clinic_requisites.egrpo,
                card_number: this.patient.card_number,
                first_appointment_date: this.$formatter.dateFormat(this.appointment.date, 'DD.MM.YYYY'),
                patient_name: this.patient.full_name,
                patient_birthday: this.patient.birthdate,
                patient_age: this.patient.age,
                primary_phone_number: this.patient.primary_phone_number,
                location: this.patient.location,
                street: this.patient.street,
                house_number: this.patient.house_number,
                apartment_number: this.patient.apartment_number,
                email: this.patient.email,
                blood_group: this.getSignalValue('blood_group'),
                rhesus_factor: this.getSignalValue('rhesus_factor'),
                transfusion: this.signalRecord.transfusion ? this.signalRecord.transfusion_comment : '',
                diabetes: this.getSignalValue('diabetes'),
                infections: this.signalRecord.infections,
                surgical_interventions: this.signalRecord.surgical_interventions,
                allergic_history: this.signalRecord.allergic_history,
                drug_intolerance: this.signalRecord.drug_intolerance,
            }
        };
    },
    mounted() {
        this.blank = new PdfBlank(this.file.file_data.url, this.$refs.docContainer, this.blankDefaults);
    },
    methods: {
        getSignalValue(category) {
            return this.signalRecord ? this.$handbook.getOption(category, this.signalRecord[category]) : ''
        },
        print() {
            this.blank.getFormData().then(() => {
                return this.blank.output();
            }).then((blob) => {
                let iframe = printer.createIframe();
                let url = window.URL.createObjectURL(blob);
                iframe.src = url;
                iframe.onload = () => {
                    setTimeout(() => {
                        iframe.focus();
                        iframe.contentWindow.print();
                        let request = fileLoader.upload(blob, this.file.name);
                        request.promise().then((response) => {
                            this.$emit('printed', {file_id: response.data.id});
                        });
                    }, 0);
                };
            });
        },
    },
}
</script>