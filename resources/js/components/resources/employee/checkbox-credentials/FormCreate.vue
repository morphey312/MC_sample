<template>
    <checkbox-credentials-form :model="model">
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
    </checkbox-credentials-form>
</template>

<script>
import CheckboxCredentialsForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import EmployeeCheckboxCredentials from "@/models/employee/checkbox-credentials";

export default {
    components: {
        CheckboxCredentialsForm,
    },
    mixins: [
        CreateMixin,
    ],
    props: {
        employee: Object,
    },
    data() {
        return {
            model: new EmployeeCheckboxCredentials({
                employee_id: this.employee.id,
            }),
        }
    },
}
</script>
