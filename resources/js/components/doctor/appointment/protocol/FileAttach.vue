<template>
    <div v-loading="saving" >
        <section>
            <el-row :gutter="20">
                <el-col :span="10">
                    <form-upload
                        ref="attachments"
                        :entity="model"
                        :multiple="false"
                        accept="image/jpeg,image/png/,application/pdf"
                        property="file_id" />
                </el-col>
            </el-row>
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
import ProtocolRecord from '@/models/patient/card/protocol-record';

export default {
    props: {
        appointment: Object,
        card: Object,
        protocol: Object,
    },
    data() {
        return {
            saving: false,
            model: new ProtocolRecord({
                card_specialization_id: this.card.id,
                appointment_id: this.appointment.id,
                template_id: this.protocol.id,
                data: [],
            }),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        save() {
            this.saving = true;
            this.model.save().then((response) => {
                this.saving = false;
                this.$info(__('Протокол исследования был успешно сохранен'));
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.saving = false;
                this.$error(__('Не удалось сохранить протокол исследования'));
            });
        }
    },
};
</script>