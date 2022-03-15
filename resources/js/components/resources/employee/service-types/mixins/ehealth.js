import ehealth from '@/services/ehealth';

export default {
    data() {
        return {
            isMakingRequest: false,
        };
    },
    methods: {
        prepareRequest(employee) {
            return new Promise((resolve) => {
                this.isMakingRequest = true;
                ehealth.createEmployeeServiceTypeRequest(this.model, employee)
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
                    }).catch(() => {
                        this.isMakingRequest = false;
                        this.$error(__('Возникла ошибка при формировании запроса в e-Health'));
                    });
            });
        },
    },
};