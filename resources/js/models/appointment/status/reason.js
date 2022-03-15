import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * Appointment Status Reason model
 */
class Reason extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Appointment_Status_Reason';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            name_lc1: null,
            name_lc2: null,
            name_lc3: null,
            esputnik_no_answer: false
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            name_lc1: maxlen(STRING_MAX_LEN),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/appointments/statuses/reasons',
            fetch: '/api/v1/appointments/statuses/reasons/{id}',
            update: '/api/v1/appointments/statuses/reasons/{id}',
            delete: '/api/v1/appointments/statuses/reasons/{id}',
        }
    }

    /**
     * Get localized name
     * 
     * @returns {String}
     */
    get name_i18n() {
        return this.getAttributeI18N('name');
    }
}

export default Reason;
