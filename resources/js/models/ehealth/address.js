import BaseModel from '@/models/base-model';

import {
    required,
    zip,
} from '@/services/validation';

class EhealthAddress extends BaseModel 
{
    /** 
     * @inheritdoc
     */
    options() {
        return {
            validateOnChange: false,
            validateOnSave: false,
        };
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            address: null,
            country: null,
            country_id: null,
            country_code: null,
            region: null,
            district: null,
            settlement: null,
            settlement_id: null,
            settlement_type: null,
            street: null,
            street_id: null,
            street_type: null,
            building: null,
            apartment: null,
            zip: null,
        };
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            address: required,
            country: required,
            country_id: required,
            country_code: required,
            region: required,
            district: required,
            settlement: required,
            settlement_id: required,
            settlement_type: required,
            street: required,
            street_id: required,
            street_type: required,
            building: required,
            apartment: required,
            zip: required.and(zip),
        };
    }
}

export default EhealthAddress;