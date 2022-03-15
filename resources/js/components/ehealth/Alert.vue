<script>
import CONSTANT from '@/constants';
import ehealth from '@/services/ehealth';

export default {
    mounted() {
        this.$eventHub.$on('broadcast.ehealth_application', (app) => {
            this.handleApplication(app.data);
        });
    },
    beforeDestroy() {
        this.$eventHub.$off('broadcast.ehealth_application');
    },
    methods: {
        handleApplication(data) {
            console.log(data);

            if (data.status === CONSTANT.EHEALTH.STATUS.SUCCESS) {
                this.showSuccess(data);
            } else if (data.status === CONSTANT.EHEALTH.STATUS.FAILED) {
                this.showFailure(data);
            } else if (data.status === CONSTANT.EHEALTH.STATUS.WAIT_AUTH) {
                this.showWaitAuth(data);
            }
        },
        showSuccess(data) {
            let kind = this.getKind(data);
            this.$info(__('Ваша заявка {kind} была успешно отправлена в eHealth.', {kind}));
        },
        showFailure(data) {
            let kind = this.getKind(data);
            let reason = this.getErrorReason(data);
            this.$error(__('Не удалось отправить вашу заявку {kind} по следующей причине: {reason}.', {kind, reason}));
        },
        showWaitAuth(data) {
            let kind = this.getKind(data);
            this.$confirm(__('Ваша заявка {kind} требует повторной авторизации в eHealth. Вы хотите авторизироваться сейчас?', {kind}), () => {
                if (data.employee.client_id) {
                    ehealth.getMspLoginUrl(data.employee.email).then((url) => {
                        location.href = url;
                    }).catch((e) => {
                        this.$error(__('Не удалось получить данные для перенаправления. Пожалуйста используйте страницу логина.'));
                    });
                } else {
                    location.href = ehealth.getMisLoginUrl();
                }
            });
        },
        getKind(data) {
            let action, subject;

            switch (data.action) {
                case CONSTANT.EHEALTH.ACTION.CREATE:
                    action = __('на создание');
                    break;
                case CONSTANT.EHEALTH.ACTION.UPDATE:
                    action = __('на обновление данных');
                    break;
                case CONSTANT.EHEALTH.ACTION.ENABLE:
                    action = __('на активацию');
                    break;
                case CONSTANT.EHEALTH.ACTION.DISABLE:
                    action = __('на деактивацию');
                    break;
                default:
                    return '';
            }

            switch (data.subject_type) {
                case 'msp':
                    subject = __('предоставителя мед. услуг');
                    break;
                case 'clinic':
                    subject = __('клиники');
                    break;
                case 'employees':
                    subject = __('сотрудника');
                    break;
                case 'msp_contract':
                    subject = __('договора');
                    break;
                case 'clinic_service_type':
                    subject = __('вида услуг');
                    break;
                case 'employee_service_type':
                    subject = __('роли сотрудника');
                    break;
                case 'ehealth_patient':
                    subject = __('пациента');
                    break;
                default:
                    return action;
            }

            return __('{action} {subject}', {action, subject});
        },
        getErrorReason(data) {
            if (data.code === 422) {
                let errors = data.invalid.map((error) => {
                    return [
                        error.property,
                        error.errors.join(', '),
                    ].join(': ');
                }).join('; ');
                return __('данные не прошли проверку: {errors}', {errors});
            }
            if (data.code === 403) {
                return __('у вас нет прав на отправку данной заявки');
            }
            if (data.code === 409) {
                return __('несоответствие данных');
            }
            return __('возникла техническая накладка');
        }
    },
    render(h) {
        return '<!-- eHEalth alert -->';
    },
};
</script>
