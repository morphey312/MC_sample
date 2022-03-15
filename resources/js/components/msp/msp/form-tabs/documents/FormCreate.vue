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
import DocumentForm from '@/components/resources/employee/documents/Form.vue';

export default {
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
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.model.validate().then((e) => {
                if (e && Object.keys(e).length !== 0) {
                    this.$displayErrors({errors: e});
                } else {
                    this.$emit('created', this.model);
                }
            });
        },
    }
}
</script>
