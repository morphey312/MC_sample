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
                ehealth.createMspRequest(this.model)
                    .then((request) => {
                        this.isMakingRequest = false;
                        if (request.hasErrors()) {
                            let errors = request.getErrorsAsObject();
                            this.$displayErrors({errors});
                        } else {
                            let data = request.getData();
                            this.signData(data, (signed) => {
                                this.model.ehealth_request = {
                                    signed,
                                    original: data,
                                };
                                resolve();
                            }, {
                                consent: ehealth.getConsentText(),
                                checkbox: __('Накладывая свою электронную подпись/квалифицированную электронную подпись, я подтверждаю достоверность указаных мною данных и даю согласие на их обработку.'),
                            });
                        }
                    }).catch(() => {
                        this.isMakingRequest = false;
                        this.$error(__('Возникла ошибка при формировании запроса в e-Health'));
                    });
            });
        }
    }
};