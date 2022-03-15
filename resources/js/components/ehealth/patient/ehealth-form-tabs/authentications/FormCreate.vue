<template>
    <section
        v-loading="loading">
        <authentication-form
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
                    @click="create">
                    {{ __('Добавить') }}
                </el-button>
            </div>
        </authentication-form>
    </section>
</template>

<script>
import AuthenticationForm from './Form.vue';
import PatientAuthentication from '@/models/ehealth/patient/authentication';
import CONSTANTS from '@/constants';
import ActionListener from './mixins/action-listener';

export default {
    mixins: [
        ActionListener
    ],
    components: {
        AuthenticationForm,
    },
    props: {
        patient: Object,
    },
    data() {
        return {
            model: new PatientAuthentication({
                ehealth_patient_id: this.patient.id,
                action: this.patient.ehealth_status === CONSTANTS.EHEALTH_PATIENT.STATUS.SIGNED ? CONSTANTS.EHEALTH_PATIENT.AUTHENTICATION_METHODS.INSERT : null,
            }),
            loading: false,
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
                    if (this.patient.ehealth_status === CONSTANTS.EHEALTH_PATIENT.STATUS.SIGNED) {
                        if (this.model.type ===  CONSTANTS.EHEALTH_PATIENT.AUTHENTICATION_TYPE.OTP) {
                            this.verifyNewPhone();
                        } else {
                            this.loading = true;
                            this.model.save();
                        }
                    } else {
                        this.$emit('created', this.model);
                    }
                }
            });
        },
    }
}
</script>
