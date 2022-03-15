import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    phoneNumber,
    missing,
    STRING_MAX_LEN
} from '@/services/validation';

/**
 * LegalEntity model
 */
class LegalEntity extends BaseModel
{
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: '',
            short_name: '',
            post_address: null,
            phone_number: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            short_name: maxlen(STRING_MAX_LEN).or(missing),
            post_address: maxlen(STRING_MAX_LEN).or(missing),
            phone_number: phoneNumber.or(missing),
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/legal-entities',
            fetch: '/api/v1/legal-entities/{id}',
            update: '/api/v1/legal-entities/{id}',
            delete: '/api/v1/legal-entities/{id}',
        }
    }

    /**
     * Get names of clinics company belongs to
     * 
     * @returns {array}
     */ 
    get clinic_names() {
        return this.entity_clinics 
            ? this.entity_clinics.map((clinic) => clinic.clinic_name)
            : [];
    }
}

export default LegalEntity;