import BaseModel from '@/models/base-model';
import Price from '@/models/price';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    requiredArray
} from '@/services/validation';

/**
 * Service model
 */
class Service extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Service';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            ehealth_service_id: null,
            name: '',
            name_lc1: null,
            name_lc2: null,
            name_lc3: null,
            name_ua: null,
            name_ua_lc1: null,
            name_ua_lc2: null,
            name_ua_lc3: null,
            specialization_id: null,
            payment_destination_id: null,
            disabled: false,
            is_for_discount_card: false,
            is_no_auto_recommend_source: false,
            is_base: false,
            is_online: false,
            diagnosis_id: null,
            for_prepayment: false,
            clinics: [],
            site_service_type: null,
            for_foreigners: false,
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            prices: (value) => this.castToInstances(Price, value),
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            name_ua: required.and(maxlen(STRING_MAX_LEN)),
            clinics: requiredArray,
            specialization_id: required,
            payment_destination_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/services',
            fetch: '/api/v1/services/{id}',
            update: '/api/v1/services/{id}',
            delete: '/api/v1/services/{id}',
        }
    }

    /**
     * Get clinic IDs
     *
     * @returns {array}
     */
    get clinic_ids() {
        return this.clinics.map((clinic) => clinic.clinic_id);
    }

    /**
     * Get localized name
     *
     * @returns {String}
     */
    get name_i18n() {
        return this.getAttributeI18N('name');
    }

    /**
     * Get localized name_ua
     *
     * @returns {String}
     */
    get name_ua_i18n() {
        return this.getAttributeI18N('name_ua');
    }
}

export default Service;
