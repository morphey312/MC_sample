import Warning from '@/components/patients/analysis-results/attachments/Warning.vue';
import BatchRequest from '@/services/batch-request';
import CONSTANT from '@/constants';

export default {
    components: {
        Warning,
    },
    props: {
        analyses: Array,
        default: () => [],
    },
    data() {
        let model = this.analyses[0];
        let modelList = this.getModelList(model);
        
        return {
            model: model.clone(),
            modelList,
            batchRequest: new BatchRequest('/api/v1/analyses/results/batch'),
            saving: false,
            fullyPaid: this.isFullyPaid(model.appointment_service),
            hasEmail: _.isFilled(model.patient.email),
            autoEmail: model.patient.mailing_analysis,
        };
    },
    computed: {
        hasSubmittedAnalysis() {
            let submitted = this.analyses.find(item => this.isSubmittedStatus(item.status));
            return submitted != undefined;
        },
    },
    methods: {
        getModelList(model) {
            return this.analyses
                .filter(item => item.analysis_id != model.analysis_id)
                .map(item => item.clone());
        },
        cancel() {
            this.$emit('cancel');
        },
        isFullyPaid(service) {
            return service && (service.payed >= service.cost);
        },
        isSubmittedStatus(status) {
            return status === CONSTANT.ANALYSIS_RESULT.STATUSES.EMAIL_SENT;
        },
        prepareRequest(data) {
            this.batchRequest.reset();
            this.batchRequest.update(this.prepareAttributes(this.model));
            this.modelList.forEach(model => {
                model.attachments = this.model.attachments;
                this.batchRequest.update(this.prepareAttributes(model, data));
            });
        },
        prepareAttributes(model, data = null) {
            model.blank_data = (data && data.blank_data) ? data.blank_data : this.model.blank_data;
            model.blank_id = (data && data.blank_id) ? data.blank_id : this.model.blank_id;

            let hasAttachments = model.attachments.length !== 0;
            let wasDeliveryAttempt = model.delivery_status !== CONSTANT.NOTIFICATION.DELIVERY_STATUS.NO_DELIVERY;
            if (model.date_ready === null && hasAttachments) {
                model.date_ready = this.$moment().format('YYYY-MM-DD');
            }
            if (hasAttachments && wasDeliveryAttempt && this.fullyPaid && this.hasEmail && this.autoEmail) {
                model.delivery_status = CONSTANT.NOTIFICATION.DELIVERY_STATUS.RE_DELIVERY;
            }
            return model;
        },
        update(data) {
            let notYetSubmitted = [...this.modelList, this.model].filter(item => {
                return !this.isSubmittedStatus(item.status);
            });
            this.prepareRequest(data);
            this.saving = true;
            
            return this.batchRequest.submit().then((result) => {
                if (result.failure.length !== 0) {
                    this.$error(__('Не удалось сохранить некоторые данные'));
                } else {
                    let nowSubmitted = 0;
                    notYetSubmitted.forEach((item) => {
                        if (this.isSubmittedStatus(item.status)) {
                            nowSubmitted++;
                        }
                    });
                    
                    if (nowSubmitted !== 0) {
                        setTimeout(() => {
                            this.$info(__('{submitted} результатов анализов были поставлены в очередь на отправку', {submitted: nowSubmitted}));
                        }, 500);
                    } else {
                        this.$info(__('Результаты успешно добавлены'));
                    }
                    return this.$emit('updated');
                }
            }).catch((error) => {
                console.log(error);
                this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
            });
        },
    }
}