<template>
    <ehealth-patient-form v-if="model !== null" :model="model">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="checkDocumentsAndAuthExist">
                {{ __('Сохранить и отправить в e-Health') }}
            </el-button>
        </div>
    </ehealth-patient-form>
    <content-placeholder v-else />
</template>

<script>
import EhealthPatientForm from './Form.vue';
import EhealthPatient from '@/models/ehealth/patient';
import ManageMixin from './mixins/manage';

export default {
    mixins: [
        ManageMixin
    ],
    components: {
        EhealthPatientForm,
    },
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            model: null,
        }
    },
    mounted() {
        let patient = new EhealthPatient({id: this.id})
        patient.fetch().then(() => {
            this.model = patient;
            this.$emit('updateStatus', patient.ehealth_status);
        })
    },
}
</script>
