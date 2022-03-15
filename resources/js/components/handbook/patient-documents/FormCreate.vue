<template>
    <protocol-template-form
        :model="model"
        :limit-clinics="$isCreationLimited('patient-documents')">
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
    </protocol-template-form>
</template>

<script>
import ProtocolTemplateForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import PatientDocument from "@/models/patient-document";

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ProtocolTemplateForm,
    },
    props: {
        is_official: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            model: new PatientDocument({is_official_form: this.is_official}),
        }
    },
}
</script>
