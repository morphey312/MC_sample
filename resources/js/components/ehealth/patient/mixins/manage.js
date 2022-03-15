import Confirm from '../Confirm.vue';
import ehealth from '@/services/ehealth';
 import OtpVerification from './otp-verification';
import CONSTANTS from '@/constants';
import RegistrationInfoViewer from '@/components/ehealth/patient/printout-forms/RegistrationInfoViewer';
import RegistrationInfoViewerHeader from '@/components/ehealth/patient/printout-forms/RegistrationInfoViewerHeader';

export default {
    mixins: [
        OtpVerification
    ],
    data() {
        return {
            errors: []
        }
    },
    mounted() {
        this.$eventHub.$on('broadcast.ehealth_application', (app) => {
            console.log(app)
            if (app.data.subject_type === 'ehealth_patient' && app.data.subject_id === this.model.id) {
                this.model.fetch().then(() => {
                    this.throwEvent(app.data);
                    this.$emit('updateStatus', this.model.status)
                })
            }
            if (app.data.subject_type === 'ehealth_patient_authentication') {
                this.$eventHub.$emit('authenticationProcess', app.data);
            }
        });
    },
    beforeDestroy() {
        this.$eventHub.$off('broadcast.ehealth_application');
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        addError(prop, error) {
            this.errors.push({prop, error});
        },
        throwEvent(data) {
            switch (data.action) {
                case CONSTANTS.EHEALTH.ACTION.UPDATE:
                    break;
                case CONSTANTS.EHEALTH.ACTION.CREATE:
                    if (data.status === CONSTANTS.EHEALTH.STATUS.SUCCESS) {
                        this.approveProcess();
                    }
                    break;
                case CONSTANTS.EHEALTH.ACTION.APPROVE:
                    if (data.status === CONSTANTS.EHEALTH.STATUS.SUCCESS) {
                        this.$eventHub.$emit('approveEhealthPatient', data.status);
                        this.showPatientInformal()
                    } else {
                        this.$eventHub.$emit('approveEhealthPatient', {status: data.status, errorMessage: data.invalid});
                    }
                    break;
                case CONSTANTS.EHEALTH.ACTION.SIGN:
                    this.$eventHub.$emit('signEhealthPatient', data.status);
                    break;
            }
        },
        getErrorsAsObject() {
            let map = {};
            this.errors.forEach((err) => {
                if (!(err.prop in map)) {
                    map[err.prop] = [];
                }
                map[err.prop].push(err.error);
            });
            return map;
        },
        checkDocumentsAndAuthExist() {
            this.errors = [];
            if (this.model.patient_documents.length === 0) {
                this.addError('documents', __('Необходимо заполнить раздел документов'));
            }
            if (this.model.patient_authentications.length === 0) {
                this.addError('authentication', __('Необходимо заполнить раздел аунтификации'));
            }

            this.confirm();
        },
        confirm() {
            this.model.validate().then((e) => {
                if (e && Object.keys(e).length !== 0) {
                    this.$displayErrors({errors: e});
                } else {
                    this.$modalComponent(Confirm, {
                        consent: ehealth.getPatientConsentText(),
                        checkbox: __('Информационная памятка сообщена пациенту.')
                    }, {
                        cancel: (dialog) => {
                            dialog.close();
                        },
                        save: (dialog, data) => {
                            dialog.close();
                            this.model.process_disclosure_data_consent = data;
                            this.model.patient_signed = false;
                            this.model.authorize_with = null;
                            this.save();
                        }
                    }, {
                        header: __('Подтверждение запроса'),
                        width: '500px',
                        customClass: 'padding-0'
                    });
                }
            });
        },
        save() {
            this.$clearErrors();
            this.model.save().then(() => {
                this.$info(__('Пациент был успешно отправлен'));
                this.$emit('updateStatus', this.model.status)
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        showPatientInformal() {
            this.model.getSignRequest().then(response => {
                this.$modalComponent(RegistrationInfoViewer, {
                    patient: this.model,
                    patientRequest: response.data
                }, {
                    close: (dialog) => {
                        dialog.close();
                    },
                }, {
                    header: '',
                    width: '1100px',
                    headerAddon: {
                        component: RegistrationInfoViewerHeader,
                        eventListeners: {
                            print: (dialog) => {
                                dialog.getTopComponent().print();
                            },
                        },
                    },
                });
            }).catch(() => {
                this.confirm();
            })
        },
    },
}
