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
        };
    },
    mounted() {
        this.blank = new PdfBlank(this.protocol.file_data.url, this.$refs.docContainer, this.protocol.result.data);
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        save() {
            let model = this.protocol.result;

            this.saving = true;
            this.blank.getFormData().then((data) => {
                model.data = data;
                return this.blank.output();
            }).then((blob) => {
                let date = this.$moment().format('YYYY-MM-DD');
                let name = `${this.protocol.name} - ${this.appointment.card_number} - ${date}.pdf`;
                let request = fileLoader.upload(blob, name);

                request.promise().then((response) => {
                    model.file_id = response.data.id;
                    model.save().then((response) => {
                        this.saving = false;
                        this.$info(__('Протокол исследования был успешно изменён'));
                        this.$emit('saved', model);
                    }).catch((e) => {
                        this.saving = false;
                        this.$error(__('Не удалось изменить протокол исследования'));
                    });
                }).catch((error) => {
                    this.saving = false;
                    this.$error(__('Не удалось загрузить изменённый протокол исследования на сервер'));
                });
            });
        }
    },
};
</script>
