import BaseModel from '@/models/base-model';
import MspOwner  from './msp/owner';
import MspArchive from './msp/archive';
import MspContract from './msp/contract';
import CONSTANT from '@/constants';
import EhealthAddress from '@/models/ehealth/address';

import {
    required,
    maxlen,
    phoneNumber,
    edrpou,
    missing,
    email,
    attributeEquals,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
    CHECKING_MAX_LEN,
} from '@/services/validation';

/**
 * Msp model
 */
class Msp extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Msp';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            ehealth_id: null,
            name: null,
            type: null,
            edrpou: null,
            address: {},
            phone: null,
            additional_phone: null,
            email: null,
            website: null,
            receiver_funds_code: null,
            beneficiary: null,
            owner_position: null,
            owner: {},
            accreditation_category: null,
            accreditation_issued_date: null,
            accreditation_expiry_date: null,
            accreditation_order_no: null,
            accreditation_order_date: null,
            license_type: null,
            license_number: null,
            license_issued_by: null,
            license_issued_date: null,
            license_expiry_date: null,
            license_active_from_date: null,
            license_subject: null,
            license_order_no: null,
            license_ehealth_id: null,
            archives: [],
            ehealth_request: null,
            checking_account: null,
            bank: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            type: required,
            edrpou: required.and(edrpou),
            address: (value) => this.validateSubmodel(value, null, ['country_code', 'region', 'settlement_id']),
            phone: required.and(phoneNumber),
            additional_phone: phoneNumber.or(missing),
            email: required.and(email),
            website: maxlen(STRING_MAX_LEN),
            receiver_funds_code: maxlen(STRING_MAX_LEN),
            beneficiary: maxlen(STRING_MAX_LEN),
            owner_position: required.and(maxlen(STRING_MAX_LEN)),
            accreditation_category: required,
            accreditation_order_no: required.or(attributeEquals('accreditation_category', CONSTANT.MSP.ACCREDITATION_CATEGORY.NONE)),
            accreditation_order_date: required.or(attributeEquals('accreditation_category', CONSTANT.MSP.ACCREDITATION_CATEGORY.NONE)),
            license_type: required,
            license_issued_by: required,
            license_issued_date: required,
            license_active_from_date: required,
            license_subject: maxlen(TEXT_MAX_LEN),
            license_order_no: required.and(maxlen(STRING_MAX_LEN)),
            checking_account: required.and(maxlen(STRING_MAX_LEN)),
            bank: required.and(maxlen(STRING_MAX_LEN)),
            owner: (value) => this.validateSubmodel(value),
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            owner: (value) => this.castToInstance(MspOwner, value),
            archives: (value) => _.isArray(value) ? value.map((archive) => this.initSubModel(MspArchive, archive)) : [],
            address: (val) => this.initSubModel(EhealthAddress, val),
            contracts: (value) => _.isArray(value) ? value.map((contract) => this.initSubModel(MspContract, contract)) : [],
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/msp',
            fetch: '/api/v1/msp/{id}',
            update: '/api/v1/msp/{id}',
            delete: '/api/v1/msp/{id}',
        }
    }

    /**
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        attributes.owner = this.owner.getSaveData();
        return attributes;
    }
}

export default Msp;
