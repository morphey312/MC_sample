<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="16" :offset="4">
                <form-select
                    :entity="model"
                    options="reason_refusing_treatment"
                    property="rejection_reason"
                    :label="__('Выберите причину не взятия лечения')" />
            </el-col>
        </el-row>
        <div class="form-footer text-right">
            <el-button
                type="primary"
                @click="close">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="default"
                @click="save">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </model-form>
</template>

<script>
import CONSTANTS from '@/constants';

export default {
    props: {
        model: Object,
    },
    data() {
        return {
        };
    },
    methods: {
        leave() {
            this.$emit('close');
            this.$router.push({name: 'doctor-schedule'});
        },
        close() {
            this.$emit('close');
        },
        save() {
            if (this.model) {
                this.model.changeStatus({system_status: CONSTANTS.APPOINTMENT.STATUSES.COMPLETED, rejection_reason: this.model.rejection_reason}).then(() => {
                    this.$emit('save');
                    this.leave();
                });
            }
        },
    },
}
</script>
