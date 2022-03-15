<template>
    <schedule-call-request-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отмена') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>    
    </schedule-call-request-form>
</template>

<script>
import AppointmentMixin from './mixins/appointment-call-request';

export default {
    mixins: [
        AppointmentMixin,
    ],
    methods: {
        update() {
            this.$clearErrors();

            if(this.isInvalidDates()){
                return this.showDateError();
            }

            this.model.save().then((response) => {
                this.$emit('updated', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    },
}
</script>

