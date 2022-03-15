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
import fileLoader from '@/services/file-loader';

export default {
    props: {
        appointment: Object,
        file: Object,
        clinicRequisites: Object,
    },
    data() {
        return {
            blank: null,
            saving: false,
            blankDefaults: {
                patient_name: this.appointment.patient_name,
                doctor_name: this.appointment.doctor_name,
                date: this.$formatter.dateFormat(this.appointment.date),
                card_number: this.appointment.card_number,
                patient_age: this.appointment.patient.age,
                patient_birthday: this.appointment.patient.birthdate,
                clinic_requisites: this.clinicRequisites.official_fullname + ', ' + this.clinicRequisites.official_address,
                clinic_egrpo: this.clinicRequisites.egrpo,
            }
        };
    },
    mounted() {
        this.blank = new PdfBlank(this.file.file_data.url, this.$refs.docContainer, this.blankDefaults);
    },
    methods: {
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
