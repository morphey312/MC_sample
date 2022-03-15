<template>
    <degree-form :model="model">
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
    </degree-form>
</template>

<script>
import EmployeeScienceDegree from '@/models/employee/science-degree';
import DegreeForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        DegreeForm,
    },
    props: {
        employee: Object,
    },
    data() {
        return {
            model: new EmployeeScienceDegree({
                employee_id: this.employee.id,
            }),
        }
    },
}
</script>
