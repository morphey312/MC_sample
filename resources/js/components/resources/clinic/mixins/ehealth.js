import ehealth from '@/services/ehealth';
import CONSTANT from '@/constants';

export default {
    data() {
        return {
            isMakingRequest: false,
        };
    },
    methods: {
        prepareRequest() {
            return new Promise((resolve) => {
                this.$confirmWhen(
                    (this.model.status === CONSTANT.CLINIC.STATUS.INACTIVE && this.model.active_in_ehealth),
                    __('Вы собираетесь деактивировать клинику в системе e-Health, вы уверены, что хотите продолжить?'),
                    () => {
                        this.isMakingRequest = true;
                        ehealth.createClinicRequest(this.model)
                            .then((request) => {
                                this.isMakingRequest = false;
                                if (request.hasErrors()) {
                                    let errors = request.getErrorsAsObject();
                                    this.$displayErrors({errors});
                                } else {
                                    let data = request.getData();
                                    this.model.ehealth_request = data;
                                    resolve();
                                }
                            }).catch((e) => {
                                this.isMakingRequest = false;
                                this.$error(__('Возникла ошибка при формировании запроса в e-Health'));
                            });
                    }
                );
            });
        },
    },
};