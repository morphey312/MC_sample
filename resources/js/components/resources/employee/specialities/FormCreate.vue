<template>
    <speciality-form :model="model">
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
    </speciality-form>
</template>

<script>
import EmployeeSpeciality from '@/models/employee/speciality';
import SpecialityForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        SpecialityForm,
    },
    props: {
        employee: Object,
    },
    data() {
        return {
            model: new EmployeeSpeciality({
                employee_id: this.employee.id,
            }),
        }
    },
}
</script>
