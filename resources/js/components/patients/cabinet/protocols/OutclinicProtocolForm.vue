<template>
    <section>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-row
                    name="protocol_name"
                    :label="__('Название протокола')">
                    <el-input
                        :placeholder="__('Введите название')"
                        v-model="model.name"/>
                </form-row>
            </el-col>
            <el-col :span="24">
                <form-row
                    name="active-clinic"
                    :label="__('Специализация')">
                    <el-select v-model="model.card_specialization_id">
                        <el-option
                            v-for="card in cards"
                            :key="card.id"
                            :value="card.id"
                            :label="card.specialization.name"
                        />
                    </el-select>
                </form-row>
                <form-row
                    name="protocol_file"
                    :label="__('Выберите файл')">
                    <form-upload
                        :key="model.id"
                        ref="attachments"
                        :on-preview="preview"
                        :entity="model"
                        :multiple="true"
                        :limit="4"
                        accept="image/jpeg,image/png/,application/pdf"
                        property="attachments"
                    />
                </form-row>
                <form-checkbox
                    v-if="!this.appointment"
                    :entity="model"
                    property="allowed_in_ok"
                    :label="__('Доступно в ЛК')"
                    />
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
import OutclinicProtocolRecord from '@/models/patient/card/outclinic-protocol-record';
import FileActionMixin from '@/mixins/file-action';

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        patient: Object,
        appointment: Object
    },
    data() {
        return {
            cards: [],
            model: null,
        };
    },
    beforeMount() {
        this.init();
    },
    methods: {
        init() {
            this.model = new OutclinicProtocolRecord({});
            if(this.appointment){
                this.model.appointment_id = this.appointment.id
            }
            this.cards = this.patient.getCardsWithSpecializations();
        },
        cancel() {
            this.$emit('cancel');
        },
        preview(file){
            this.view(file.response.url, file.name)
        },
        create() {
            this.model.save().then((response) => {
                if (!response.response || !response.response.data) {
                    this.$error(__('Не удалось сохранить результат исследования'));
                    return;
                }
                this.$info(__('Результат исследования успешно сохранён'));
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    }
}
</script>
