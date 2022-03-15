import OtpVerification from '@/components/ehealth/patient/mixins/otp-verification';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        OtpVerification
    ],
    mounted() {
        this.$eventHub.$on('authenticationProcess', (data) => {
            if (data.subject_id === this.model.id) {
                this.chooseAction(data);
            }
        });
    },
    beforeDestroy() {
        this.$eventHub.$off('authenticationProcess');
    },
    methods: {
        verifyNewPhone() {
            this.loading = true;
            this.initializeOtp(this.model.phone_number);
            this.showApproveOtp(this.model.phone_number);
        },
        chooseAction(data) {
            switch (data.action) {
                case CONSTANTS.EHEALTH.ACTION.UPDATE:
                    if (data.status === CONSTANTS.EHEALTH.STATUS.SUCCESS) {
                        this.model.fetch().then(() => {
                            this.approveProcess();
                        })
                    }
                case CONSTANTS.EHEALTH.ACTION.DISABLE:
                    if (data.status === CONSTANTS.EHEALTH.STATUS.SUCCESS) {
                        this.model.fetch().then(() => {
                            this.approveProcess();
                        })
                    }
                    break;
                case CONSTANTS.EHEALTH.ACTION.CREATE:
                    if (data.status === CONSTANTS.EHEALTH.STATUS.SUCCESS) {
                        this.model.fetch().then(() => {
                            if (this.model.type === CONSTANTS.EHEALTH_PATIENT.AUTHENTICATION_TYPE.OTP &&
                                this.model.urgent.authentication_method_current &&
                                this.model.urgent.authentication_method_current.type === CONSTANTS.EHEALTH_PATIENT.AUTHENTICATION_TYPE.OTP.toUpperCase()) {
                                this.approveProcess(true);
                            } else {
                                this.approveProcess();
                            }
                        })
                    } else {
                        this.loading = false
                        if (this.model.type === CONSTANTS.EHEALTH_PATIENT.AUTHENTICATION_TYPE.THIRD_PERSON) {
                            this.$error(__('Номер телефона не принадлежит третьему лицу'))
                        }
                    }
                    break;
                case CONSTANTS.EHEALTH.ACTION.APPROVE:
                    if (data.status === CONSTANTS.EHEALTH.STATUS.SUCCESS) {
                        this.$eventHub.$emit('approveEhealthPatientAuthentication',{status: data.status, errorMessage: null});
                        this.$emit('done', this.model);
                    } else {
                        this.$eventHub.$emit('approveEhealthPatientAuthentication', {status: data.status, errorMessage: data.invalid});
                    }
                    break;
            }
        },
    },
}
