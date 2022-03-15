<template>
    <section>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-row
                    name="protocol_name"
                    :label="__('Имя документа')">
                    <el-input
                        v-model="model.name"
                        :placeholder="__('Введите имя')"
                    />
                </form-row>
            </el-col>
            <el-col :span="8">
                <form-upload
                    ref="attachments"
                    :entity="model"
                    :limit="1"
                    accept="image/jpeg,image/png/,application/pdf"
                    property="attachments" />
            </el-col>
        </el-row>
        <div class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </section>
</template>
<script>
import Document from '@/models/patient/card/document';

export default {
    props: {
        appointment: Object,
        card: Object,
    },
    data() {
        return {
            model: null,
        };
    },
    beforeMount() {
        this.init();
    },
    methods: {
        init() {
            if (this.card) {
                this.model = new Document({
                    card_specialization_id: this.card.id,
                    appointment_id: this.appointment.id,
                    patient_id: this.appointment.patient_id,
                });
                return;
            }

            this.$error(__('У пациента нет карты'));
            this.cancel();
        },
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.model.save().then((response) => {
                if (!response.response || !response.response.data) {
                    this.$error(__('Не удалось сохранить данные'));
                    return;
                }
                this.$info(__('Документ успешно сохранены'));
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    }
}
</script>
