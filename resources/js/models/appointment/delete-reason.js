import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * AppointmentStatus model
 */
class AppointmentDeleteReason extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Appointment_DeleteReason';
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
            include_to_report: true,
            not_use_for_appointment_delete: false,
            comment_required: true,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/appointments/delete-reasons',
            fetch: '/api/v1/appointments/delete-reasons/{id}',
            update: '/api/v1/appointments/delete-reasons/{id}',
            delete: '/api/v1/appointments/delete-reasons/{id}',
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

export default AppointmentDeleteReason;