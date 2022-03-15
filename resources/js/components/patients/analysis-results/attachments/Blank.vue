<template>
    <div v-loading="saving">
        <warning
            :was-submitted="isSubmittedStatus(model.status)"
            :fully-paid="fullyPaid"
            :has-email="hasEmail"
            :auto-email="autoEmail"/>
        <section
            ref="docContainer"
            class="light-grey pdf-container">
        </section>
        <section
            ref="hiddenDocContainer"
            style="display: none;">
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
import AttachmentMixin from '../mixins/attachment';
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
        let patient = this.analysis.patient;

        return {
            patient,
            blank: null,
            stampBlank: null,
            blankDefaults: {
                date: this.analysis.date_pass,
                patient_name: patient.full_name_latin ? patient.full_name + '/' + patient.full_name_latin : patient.full_name,
                card_number: patient.card_no,
                verification_code: this.analysis.verification_code,
                patient_age: this.calcAge(patient.birthday),
                patient_gender: this.$handbook.getOption('gender_short', patient.gender),
                patient_birthday: this.$moment(patient.birthday).format('DD.MM.YYYY'),
                patient_passport: patient.passport_no,
                qr_code_url: this.analysis.analysis.clinic.analysis_check_url !== null ?
                    this.analysis.analysis.clinic.analysis_check_url + this.analysis.verification_code : null,
                ...(this.analysis.blank_id === this.template.file_id ? this.analysis.blank_data : {}),
            },
        };
    },
    mounted() {
        this.blank = new PdfBlank(this.template.file_data.url, this.$refs.docContainer, this.blankDefaults);

        if (this.template.stamp_file_data) {
            this.getImage(this.template.stamp_file_data.url).then((blob) => {
                let img = new Image();
                img.src = blob;
                img.onload = () => {
                    this.stampBlank = new PdfBlank(this.template.file_data.url,
                        this.$refs.hiddenDocContainer, this.blankDefaults,
                        [{name: 'stamp', img: img}]
                    );
                };
            })
        }
    },
    methods: {
        calcAge(birthday) {
            if (birthday) {
                return this.$moment().diff(birthday, 'years');
            }
            return '';
        },
        getImage(url) {
            return fileLoader.get(url);
        },
        save() {
            this.saving = true;
            this.blank.getFormData().then((data) => {
                this.model.blank_data = data;
                if (this.stampBlank) {
                    this.stampBlank.setFormData({
                        ...this.blankDefaults,
                        ...data
                    });
                }
                this.model.blank_id = this.template.file_id;
                return this.blank.output();
            }).then((blob) => {
                let date = this.$moment().format('YYYY-MM-DD');
                let name = `${this.patient.full_name} - ${this.patient.card_number} - ${date}_no-stamp.pdf`;
                let nameStamped = `${this.patient.full_name} - ${this.patient.card_number} - ${date}_stamped.pdf`;
                let requestNotStamped = fileLoader.upload(blob, name).promise();
                if (this.stampBlank) {
                    this.stampBlank.output().then((stampedBlob) => {
                        let requestStamped = fileLoader.upload(stampedBlob, nameStamped).promise();

                        Promise.all([requestNotStamped, requestStamped]).then(results => {
                            this.saving = false;
                            this.model.attachments = [
                                results[1].data.id,  //stamped
                                results[0].data.id   //no stamp
                            ];
                            this.update();
                        }).catch((error) => {
                            console.log(error);
                            this.saving = false;
                            this.$error(__('Не удалось загрузить файл результата на сервер'));
                        });

                    })
                } else {
                    requestNotStamped.then((response) => {
                        this.saving = false;
                        this.model.attachments = [response.data.id];
                        this.update();
                    }).catch((error) => {
                        this.saving = false;
                        this.$error(__('Не удалось загрузить файл результата на сервер'));
                    });
                }
            });
        },
    },
}
</script>
