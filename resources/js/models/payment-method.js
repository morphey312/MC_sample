import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    requiredArray,
} from '@/services/validation';

/**
 * PaymentMethod model
 */
class PaymentMethod extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'PaymentMethod';
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
            use_cash: false,
            for_checkbox: false,
            is_enabled: true,
            online_payment: false,
            pc_payment: false,
            change_payment_date: false,
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
            create: '/api/v1/payment-methods',
            fetch: '/api/v1/payment-methods/{id}',
            update: '/api/v1/payment-methods/{id}',
            delete: '/api/v1/payment-methods/{id}',
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

export default PaymentMethod;
