<template>
    <ehealth-patient-form :model="model">
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
        initialData: {
            type: Object,
            required: false,
        },
        patient: {
            type: Object,
            required: false,
        },
    },
    data() {
        return {
            model: new EhealthPatient({
                first_name: this.initialData.first_name,
                last_name: this.initialData.last_name,
                second_name: this.initialData.second_name,
                birth_date: this.initialData.birth_date,
                patient_id: this.patient ? this.patient.id : null,
                gender: this.patient ? this.patient.gender : null,
            }),
        }
    },
}
</script>
