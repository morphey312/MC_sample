<template>
    <qualification-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </qualification-form>
</template>

<script>
import EmployeeQualification from '@/models/employee/qualification';
import QualificationForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        QualificationForm,
    },
    props: {
        employee: Object,
    },
    data() {
        return {
            model: new EmployeeQualification({
                employee_id: this.employee.id,
            }),
        }
    },
}
</script>
