<template>
    <section
        v-loading="loading">
        <document-form
            :model="model"
            :patient="patient">
            <div
                slot="buttons"
                class="form-footer text-right">
                <el-button
                    @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    @click="update">
                    {{ __('Сохранить') }}
                </el-button>
            </div>
        </document-form>
    </section>
</template>

<script>
import DocumentForm from './Form.vue';
import CONSTANTS from '@/constants';
import PatientAuthentication from '@/models/ehealth/patient/authentication';
import ActionListener from './mixins/action-listener';

export default {
    mixins: [
        ActionListener
    ],
    components: {
        DocumentForm,
    },
    props: {
        item: Object,
        patient: Object
    },
    data() {
        return {
            model: new PatientAuthentication({
                action: CONSTANTS.EHEALTH_PATIENT.AUTHENTICATION_METHODS.UPDATE,
                ehealth_patient_id: this.patient.id,
                ehealth_id: this.item.id,
                type: this.item.type,
                value: this.item.value,
                alias: this.item.alias,
            }),
            loading: false,
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        update() {
            this.model.validate().then((e) => {
                if (e && Object.keys(e).length !== 0) {
                    this.$displayErrors({errors: e});
                } else {
                    this.loading = true;
                    this.model.save();
                }
            });
        },
    }
}
</script>
