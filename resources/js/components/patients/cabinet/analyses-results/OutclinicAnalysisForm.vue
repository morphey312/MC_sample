<template>
    <section>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-row
                    name="protocol_name"
                    :label="__('Название анализа')">
                    <el-input
                        v-model="model.custom_name"
                        :placeholder="__('Введите название')"/>
                </form-row>
                <el-row>
                    <el-col :span="12">
                        <form-row
                            name="protocol_file"
                            :label="__('Выберите файл')"
                        >
                            <form-upload
                                ref="attachments"
                                :entity="model"
                                :limit="1"
                                property="attachments"
                            />
                        </form-row>
                    </el-col>

                </el-row>
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

import Result from '@/models/analysis/result';
import moment from 'moment'

export default {
    props: {
        patient: Object,
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
            this.model = new Result({
                patient_id : this.patient.id,
                status: 'ready',
                clinic_id: this.patient.clinics[0],
                date_ready : moment().format("YYYY-MM-DD"),
                date_pass : moment().format("YYYY-MM-DD"),
                is_outclinic: true
            });
        },
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.model.save().then((response) => {
                if (!response.response || !response.response.data) {
                    this.$error(__('Не удалось сохранить результат анализа'));
                    return;
                }
                this.$info(__('Результат анализа успешно сохранены'));
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    }
}
</script>
