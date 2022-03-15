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
class Shift extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Shift';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            employee_id: null,
            clinic_id: null,
            login: null,
            password: null,
            cashbox_key: null,
            access_token: null,
            money_reciever_id: null,
            money_reciever: null,
            money_reciever_cashbox_id: null,
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/checkbox/shifts',
            fetch: '/api/v1/checkbox/shifts/{id}',
            update: '/api/v1/checkbox/shifts/{id}',
            delete: '/api/v1/checkbox/shifts/{id}',
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

export default Shift;
