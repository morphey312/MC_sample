import BaseModel from '@/models/base-model';
import PriceSet from '@/models/price/set';

import {
    required,
    maxlen,
    phoneNumber,
    missing,
    STRING_MAX_LEN
} from '@/services/validation';

class InsuranceCompany extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            short_name: null,
            location: null,
            post_address: null,
            phone_number: null,
            bank_account: null,
            egrpo: null,
            tax_number: null,
            signer: null,
            signer_position: null,
            show_price: true,
            price_set: null,
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            price_set: (val) => val ? this.initSubModel(PriceSet, val) : null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            short_name: maxlen(STRING_MAX_LEN).or(missing),
            location: maxlen(STRING_MAX_LEN).or(missing),
            post_address: maxlen(STRING_MAX_LEN).or(missing),
            phone_number: phoneNumber.or(missing),
            bank_account: maxlen(STRING_MAX_LEN).or(missing),
            egrpo: maxlen(STRING_MAX_LEN).or(missing),
            tax_number: maxlen(STRING_MAX_LEN).or(missing),
            signer: maxlen(STRING_MAX_LEN).or(missing),
            signer_position: maxlen(STRING_MAX_LEN).or(missing),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/insurance-companies',
            fetch: '/api/v1/insurance-companies/{id}',
            update: '/api/v1/insurance-companies/{id}',
            delete: '/api/v1/insurance-companies/{id}',
        }
    }

    /**
     * Get agreements of clinics company belongs to
     * 
     * @returns {array}
     */ 
    get agreements() {
        return this.company_clinics
            ? this.company_clinics.map(clinic => clinic.agreement)
            : [];
    }

    /**
     * Get names of clinics company belongs to
     * 
     * @returns {array}
     */ 
    get clinic_names() {
        return this.company_clinics 
            ? this.company_clinics.map((clinic) => clinic.clinic_name)
            : [];
    }
}

export default InsuranceCompany;