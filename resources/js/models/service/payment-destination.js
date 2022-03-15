import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    requiredArray,
} from '@/services/validation';

/**
 * AppointmentStatus model
 */
class PaymentDestination extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Service_PaymentDestination';
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
            color: null,
            payment_destination_reference_id: null,
            disabled: false,
            additional_service_mark: null,
            include_in_specialization_report: false,
            clinics: [],
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
            clinics: requiredArray,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/services/payment-destinations',
            fetch: '/api/v1/services/payment-destinations/{id}',
            update: '/api/v1/services/payment-destinations/{id}',
            delete: '/api/v1/services/payment-destinations/{id}',
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

export default PaymentDestination;
