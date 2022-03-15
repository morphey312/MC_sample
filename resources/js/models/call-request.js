import BaseModel from '@/models/base-model';
import {
    required,
    date,
    assertion,
} from '@/services/validation';

/**
 * CallRequest model
 */
class CallRequest extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            added: 'manual',
            status: 'made',
            original_status: null,
            recall_from: '',
            recall_to: '',
            comment: '',
            comment_canceled: '',
            clinic_id: null,
            call_request_purpose_id: null,
            specialization_id: null,
            doctor_id: null,
            doctor_type: null,
            patient_id: null,
            recommended_appointment_date: null,
            appointment_id: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            call_request_purpose_id: required,
            clinic_id: required,
            recall_from: required.and(date),
            recall_to: required.and(date),
            comment_canceled: assertion(() => {
                return this.status !== 'canceled';
            }).or(required).format(__('Укажите причину отмены заявки на прозвон')),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/call-requests',
            fetch: '/api/v1/call-requests/{id}',
            update: '/api/v1/call-requests/{id}',
        }
    }

    /**
     * Get recall period
     *
     * @returns {string}
     */
    get recall_period() {
        return {
            from: this.recall_from,
            to: this.recall_to,
        };
    }
}

export default CallRequest;
