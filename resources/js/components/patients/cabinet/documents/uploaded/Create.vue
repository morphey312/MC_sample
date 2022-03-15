<template>
    <document-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create" >
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </document-form>
</template>

<script>
import DocumentForm from './Form.vue';
import UploadedDocument from '@/models/patient/uploaded-document';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        DocumentForm,
    },
    props: {
        patient: Object
    },
    data() {
        return {
            model: new UploadedDocument({
                patient_id: this.patient.id,
            }),
        }
    },
}
</script>