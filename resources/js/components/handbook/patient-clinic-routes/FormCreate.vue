<template>
    <document-template-form
        :model="model"
        :limit-clinics="$isCreationLimited('patient-clinic-routes')">
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
    </document-template-form>
</template>

<script>
import DocumentTemplateForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import PatientClinicRoute from "@/models/patient/clinic-route";

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        DocumentTemplateForm,
    },
    data() {
        return {
            model: new PatientClinicRoute(),
        }
    },
}
</script>
