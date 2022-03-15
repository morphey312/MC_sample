import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * AppointmentStatus model
 */
class AppointmentStatus extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Appointment_Status';
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
            comment_required: false,
            status_reason: false,
            is_active: true,
            service_in_cost: true,
            patient_card_required: false,
            service_in_order: false,
            sms_for_card: false,
            for_surgery: false,
            color: '',
            reasons: [],
            has_delay: false,
            delay: null,
            delay_reasons: [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            name_lc1: maxlen(STRING_MAX_LEN),
            name_lc2: maxlen(STRING_MAX_LEN),
            name_lc3: maxlen(STRING_MAX_LEN),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/appointments/statuses',
            fetch: '/api/v1/appointments/statuses/{id}',
            update: '/api/v1/appointments/statuses/{id}',
            delete: '/api/v1/appointments/statuses/{id}',
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

export default AppointmentStatus;