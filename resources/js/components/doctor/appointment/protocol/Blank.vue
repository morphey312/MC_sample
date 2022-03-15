<template>
    <div v-loading="blank === null || !blank.ready || saving" >
        <section
            ref="docContainer"
            class="light-grey pdf-container">
        </section>
        <div class="dialog-footer text-right">
            <el-button
                type="default"
                @click="cancel">
                {{ __('Отмена') }}
            </el-button>
            <el-button
                type="default"
                @click="save">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import PdfBlank from '@/services/pdf-blank';
import ProtocolRecord from '@/models/patient/card/protocol-record';
import fileLoader from '@/services/file-loader';

export default {
    props: {
        appointment: Object,
        card: Object,
        protocol: Object,
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
            }
        };
    },
    mounted() {
        this.blank = new PdfBlank(this.protocol.file_data.url, this.$refs.docContainer, this.blankDefaults);
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        save() {
            let model = new ProtocolRecord({
                card_specialization_id: this.card.id,
                appointment_id: this.appointment.id,
                template_id: this.protocol.id,
            });

            this.saving = true;
            this.blank.getFormData().then((data) => {
                model.data = data;
                return this.blank.output();
            }).then((blob) => {
                let date = this.$moment().format('YYYY-MM-DD')
                let name = `${this.protocol.name} - ${this.appointment.card_number} - ${date}.pdf`;
                let request = fileLoader.upload(blob, name);

                request.promise().then((response) => {
                    model.file_id = response.data.id;
                    model.save().then((response) => {
                        this.saving = false;
                        this.$info(__('Протокол исследования был успешно сохранен'));
                        this.$emit('saved', model);
                    }).catch((e) => {
                        this.saving = false;
                        this.$error(__('Не удалось сохранить протокол исследования'));
                    });
                }).catch((error) => {
                    this.saving = false;
                    this.$error(__('Не удалось загрузить протокол исследования на сервер'));
                });
            });
        }
    },
};
</script>
