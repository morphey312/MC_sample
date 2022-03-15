<template>
    <education-form :model="model">
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
    </education-form>
</template>

<script>
import EmployeeEducation from '@/models/employee/education';
import EducationForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        EducationForm,
    },
    props: {
        employee: Object,
    },
    data() {
        return {
            model: new EmployeeEducation({
                employee_id: this.employee.id,
            }),
        }
    },
}
</script>
