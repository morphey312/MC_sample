<template>
    <plan-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create" >
                {{ __('Добавить') }}
            </el-button>
        </div>
    </plan-form>
</template>

<script>
import PlanForm from './Form.vue';
import DoctorIncomePlan from '@/models/employee/doctor-income-plan';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        PlanForm,
    },
    data() {
        return {
            model: new DoctorIncomePlan(),
        }
    },
}
</script>