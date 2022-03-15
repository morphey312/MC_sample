<template>
    <reason-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create">
                {{ __('Создать') }}
            </el-button>
        </div>
    </reason-form>
</template>

<script>
import ReasonForm from './Form.vue';
import AppointmentDeleteReason from '@/models/appointment/delete-reason';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ReasonForm,
    },
    data() {
        return {
            model: new AppointmentDeleteReason(),
        }
    },
}
</script>