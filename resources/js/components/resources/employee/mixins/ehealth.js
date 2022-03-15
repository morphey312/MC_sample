import ehealth from '@/services/ehealth';
import DigitalSignMixin from '@/mixins/digital-sign';

export default {
    mixins: [
        DigitalSignMixin,
    ],
    data() {
        return {
            isMakingRequest: false,
        };
    },
    methods: {
        prepareRequest() {
            return new Promise((resolve) => {
                this.isMakingRequest = true;
                ehealth.createEmployeeRequest(this.model)
                    .then((request) => {
                        this.isMakingRequest = false;
                        let data = request.getData();
                        let dismissed = data.employee_request.status === 'DISMISSED';
                        if (dismissed) {
                            let message = __('Внимание! Увольнение сотрудника в электронной системе охраны здоровья является необратимой операцией. Совершайте увольнение сотрудника после выполнения соответствующих действий на предприятии и декативации всех ролей сотрудника.');
                            if (data.employee_request.doctor) {
                                message += ' ' + __('Оповещаем, то все действующие декларации сотрудника автоматически прекратят действие.');
                            }
                            this.$confirm(message, () => {
                                this.model.ehealth_request = {
                                    disabled: true,
                                };
                                resolve();
                            }, {
                                confirmBtnText: __('Продолжить'),
                            });
                        } else {
                            if (request.hasErrors()) {
                                let errors = request.getErrorsAsObject();
                                this.$displayErrors({errors});
                            } else {
                                this.signData(data, (signed) => {
                                    this.model.ehealth_request = {
                                        signed,
                                        original: data,
                                    };
                                    resolve();
                                });
                            }
                        }
                    }).catch(() => {
                        this.isMakingRequest = false;
                        this.$error(__('Возникла ошибка при формировании запроса в e-Health'));
                    });
            });
        }
    }
};