<template>
    <clinic-form 
        :model="model"
        :limit-clinics="limitClinics">
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
    </clinic-form>
</template>

<script>
import EmployeeClinic from '@/models/employee/clinic';
import ClinicForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ClinicForm
    },
    props: {
        employee: Object,
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            model: new EmployeeClinic({
                employee_id: this.employee.id,
            }),
        }
    },
}
</script>
