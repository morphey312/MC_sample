<template>
    <document-form :model="model">
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
    </document-form>
</template>

<script>
import EmployeeDocument from '@/models/employee/document';
import DocumentForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        DocumentForm,
    },
    props: {
        employee: Object,
    },
    data() {
        return {
            model: new EmployeeDocument({
                employee_id: this.employee.id,
            }),
        }
    },
}
</script>
