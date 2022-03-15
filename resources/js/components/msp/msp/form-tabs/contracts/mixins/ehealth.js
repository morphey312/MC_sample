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
                ehealth.createMspContractRequest(this.model, this.msp)
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
                                consent: ehealth.getNHSConsentText(this.model.type),
                                checkbox: __('Накладывая свою электронную подпись/квалифицированную электронную подпись, я осознаю наступление определенных прав и обязательств, понял текст договора.'),
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