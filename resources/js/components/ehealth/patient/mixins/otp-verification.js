import confirmAction from '@/components/ehealth/patient/authentications/FormOtp.vue';
import ehealth from '@/services/ehealth';

export default {
    data() {
        return {
        }
    },
    methods: {
        approveProcess(showPhoneNumberInfo = false) {
            this.showApproveOtp(this.model.urgent.authentication_method_current.phone_number, true, showPhoneNumberInfo)
        },
        showApproveOtp(phoneNumber, approveAction = false, showPhoneNumberInfo= false) {
            let hidePhoneNumber = phoneNumber;

            if (!approveAction) {
                hidePhoneNumber = phoneNumber.slice(3, 6) + '*****' + phoneNumber.slice(11)
            }

            this.$modalComponent(confirmAction, {
                model: this.model,
                approveAction: approveAction,
                phoneNumber: hidePhoneNumber,
                showPhoneNumberInfo: showPhoneNumberInfo
            }, {
                resendSms: () => {
                    this.model.resendOtp().then(() => {
                        this.$info(__('СМС с кодом подтверждения отправлена пациенту'));
                    })
                },
                reInitializeOtp: () => {
                    this.initializeOtp(phoneNumber);
                },
                approve: (dialog, {code, documents}) => {
                    this.model.approve(code, documents).then(() => {
                        this.loading = true;
                    }).catch(() => {
                        dialog.getTopComponent().stopLoading();
                        this.$error(__('Возникла ошибка при формировании запроса в e-Health'));
                    });
                },
                completeOtp: (dialog, code) => {
                    ehealth.completeOtp(code, phoneNumber).then(() => {
                        this.$info(__('Телефон был подтвержден успешно'));
                        this.model.save()
                        dialog.close();
                    }).catch(() => {
                        dialog.getTopComponent().stopLoading();
                        this.$error(__('Возникла ошибка при формировании запроса в e-Health'));
                    });
                },
                cancel: (dialog) => {
                    dialog.close();
                    this.model.documents_for_upload = []
                },
            }, {
                header: __('Подтверждение запроса'),
                width: '500px',
                customClass: 'no-footer',
            });
        },
        initializeOtp(phoneNumber) {
            ehealth.initializeOtp(phoneNumber).then(() => {
                this.$info(__('СМС с кодом подтверждения отправлена пациенту'));
            });
        }
    }
}
