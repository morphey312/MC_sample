<template>
    <div v-loading="saving">
        <warning 
            :was-submitted="hasSubmittedAnalysis"
            :fully-paid="fullyPaid"
            :has-email="hasEmail"
            :auto-email="autoEmail" />
        <section
            ref="docContainer"
            class="light-grey pdf-container">
        </section>
        <div class="dialog-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="save">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import AttachmentMixin from './mixins/attachment';
import PdfBlank from '@/services/pdf-blank';
import fileLoader from '@/services/file-loader';

export default {
    mixins: [
        AttachmentMixin,
    ],
    props: {
        template: Object,
    },
    data() {
        let analysis = this.analyses[0];
        let patient = analysis.patient;

        return {
            patient,
            blank: null,
            blankDefaults: {
                patient_name: patient.full_name,
                date: this.$moment().format('DD.MM.YYYY'),
                card_number: patient.card_no,
                patient_age: this.caclAge(patient.birthday),
                patient_gender: this.$handbook.getOption('gender_short', patient.gender),
                patient_birthday: this.$moment(patient.birthday).format('DD.MM.YYYY'),
                patient_passport: patient.passport_no,
                ...(analysis.blank_id === this.template.file_id ? analysis.blank_data : {}),
            },
        };
    },
    mounted() {
        this.blank = new PdfBlank(this.template.file_data.url, this.$refs.docContainer, this.blankDefaults);
    },
    methods: {
        caclAge(birthday) {
            if (birthday) {
                return this.$moment().diff(birthday, 'years');
            }
            return '';
        },
        save() {
            this.saving = true;
            this.blank.getFormData().then((data) => {
                this.model.blank_data = data;
                this.model.blank_id = this.template.file_id;
                return this.blank.output();
            }).then((blob) => {
                let date = this.$moment().format('YYYY-MM-DD');
                let name = `${this.template.name} - ${this.patient.card_number} - ${date}.pdf`;
                let request = fileLoader.upload(blob, name);
                request.promise().then((response) => {
                    this.saving = false;
                    let attachments = [response.data.id];
                    this.model.attachments = attachments;
                    this.update({blank_data: this.model.blank_data, blank_id: this.template.file_id});
                }).catch((error) => {
                    console.log(error);
                    this.saving = false;
                    this.$error(__('Не удалось загрузить файл результата на сервер'));
                });
            });
        },
    },
}   
</script>