import CONSTANT from '@/constants';
import Warning from '../attachments/Warning.vue';

export default {
    components: {
        Warning,
    },
    props: {
        analysis: Object,
    },
    data() {
        return {
            saving: false,
            model: this.analysis.clone(),
            fullyPaid: this.isFullyPaid(this.analysis.appointment_service),
            hasEmail: _.isFilled(this.analysis.patient.email),
            autoEmail: this.analysis.patient.mailing_analysis,
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        isFullyPaid(service) {
            return service && (service.payed >= service.cost);
        },
        isSubmittedStatus(status) {
            return status === CONSTANT.ANALYSIS_RESULT.STATUSES.EMAIL_SENT;
        },
        update() {
            let wasSubmitted = this.isSubmittedStatus(this.model.status);
            let hasAttachments = this.model.attachments.length !== 0;
            let wasDeliveryAttempt = this.model.delivery_status !== CONSTANT.NOTIFICATION.DELIVERY_STATUS.NO_DELIVERY;
            if (this.model.date_ready === null && hasAttachments) {
                this.model.date_ready = this.$moment().format('YYYY-MM-DD');
            }
            if (hasAttachments && wasDeliveryAttempt && this.fullyPaid && this.hasEmail && this.autoEmail) {
                this.model.delivery_status = CONSTANT.NOTIFICATION.DELIVERY_STATUS.RE_DELIVERY;
                wasSubmitted = false;
            }
            this.saving = true;
            this.model.withScopes(['attachments']).save().then(() => {
                this.saving = false;

                let nowSubmitted = this.isSubmittedStatus(this.model.status);
                if (!wasSubmitted && nowSubmitted) {
                    this.$info(__('Результаты успешно сохранены и поставлены в очередь на отправку.'));
                } else {
                    this.$info(__('Результаты успешно сохранены.'));
                }

                this.$emit('updated', {
                    status: this.model.status,
                    date_ready: this.model.date_ready,
                    date_sent_email: this.model.date_sent_email,
                    delivery_status: this.model.delivery_status,
                    attachments: this.model.attachments,
                    attachments_data: this.model._attributes.attachments_data,
                    blank_id: this.model.blank_id,
                    blank_data: this.model.blank_data,
                });
            }).catch((e) => {
                this.saving = false;
                this.$displayErrors(e);
            });
        },
    }
}