<template>
    <div v-loading="blank === null || !blank.ready || saving">
        <section
            id="blank-container"
            ref="docContainer"
            class="light-grey pdf-container"
        >
        </section>
        <div
            class="form-footer text-right"
        >
            <el-button
                @click="cancel"
            >
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="save"
            >
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import PdfBlank from '@/services/pdf-blank';
import fileLoader from '@/services/file-loader';
import Clinic from "@/models/clinic";
import Document from '@/models/patient/card/document';
import Patient from '@/models/patient';

export default {
    props: {
        clinicId: [String, Number],
        patientId: [String, Number],
    },
    data() {
        return {
            blank: null,
            saving: false,
            patient: null
        };
    },
    mounted() {
        new Clinic({id: this.clinicId}).fetch(['questionnaire'])
            .then((clinic) => {
                this.patient = new Patient({id: this.patientId});
                this.patient.fetch([
                    'cards',
                    'clinics',
                    'contact_details']).then((response) => {
                    let blankDefaults = {
                        firstname: response.response.data.firstname,
                        lastname: response.response.data.lastname,
                        middlename: response.response.data.middlename,
                        adress: response.response.data.location,
                        street: response.response.data.street,
                        phone: response.response.data.contact_details.primary_phone_number,
                        email: response.response.data.contact_details.email,
                        house_number: response.response.data.house_number,
                        apartment_number: response.response.data.apartment_number,
                        patient_age: response.response.data.age,
                        birthday: response.response.data.birthday ?
                            this.$moment(response.response.data.birthday, 'YYYY-MM-DD').format('DD.MM.YYYY') : '',
                        insurance: response.response.data.med_insurance === 'yes',
                    }

                    this.blank = new PdfBlank(clinic.response.data.questionnaire_blank_file.url, this.$refs.docContainer, blankDefaults);
                })
            });
    },
    methods: {
        save() {
            this.saving = true;
            this.$clearErrors();
            this.blank.getFormData().then((data) => {
                this.patient.firstname = data.firstname;
                this.patient.lastname = data.lastname;
                this.patient.middlename = data.middlename;
                this.patient.location = data.adress;
                this.patient.street = data.street;
                this.patient.contact_details = {
                    primary_phone_number: data.phone,
                    email: data.email,
                };
                this.patient.house_number = data.house_number;
                this.patient.apartment_number = data.apartment_number;
                this.patient.birthday = this.$moment(data.birthday,'DD.MM.YYYY' ).format('YYYY-MM-DD');
                this.patient.med_insurance = data.insurance ? 'yes' : 'no';
                this.patient.updateQuestionnaire();
                return this.blank.output();
            }).then((blob) => {
                let name = __('Анкета пациента.pdf');
                let request = fileLoader.upload(blob, name);

                let cards = this.patient.getCardsWithSpecializations();
                if(cards.length){
                    request.promise().then((response) => {
                        let document = new Document({
                            card_specialization_id: cards[0].id,
                            patient_id: this.patient.id,
                            is_questionnaire: true,
                        });

                        document.attachments = [response.data.id];
                        document.name = name;
                        document.save().then(() => {
                            this.saving = false;
                            this.$info(__('Спасибо, ваша анкета сохранена !'));
                            this.$emit('saved', document);
                            this.cancel();
                        }).catch((e) => {
                            console.log(e);
                            this.saving = false;
                            this.$error(__('Извинте, не удалось сохранить анкету, обратитесь к сотруднику.'));
                        });
                    }).catch((e) => {
                        console.log(e);
                        this.saving = false;
                        this.$error(__('Извинте, не удалось сохранить анкету, обратитесь к сотруднику. Ошибка 2'));
                    });
                }else {
                    this.$error(__('Создайте карту пациента'));
                }
            });
        },
        cancel(){
            this.$emit('close');
        },
    },
}
</script>
