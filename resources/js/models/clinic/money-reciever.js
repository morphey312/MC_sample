import BaseModel from '@/models/base-model';
import {
    maxlen,
    required,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
    edrpou,
    iban,
} from '@/services/validation';

/**
 * Clinic money reciever model
 */
class MoneyReciever extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            signer: null,
            official_additional: null,
            signer_position: null,
            bank: null,
            bank_account: null,
            edrpou: null,
            cashboxes: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            signer: required.and(maxlen(STRING_MAX_LEN)),
            bank: required.and(maxlen(STRING_MAX_LEN)),
            official_additional: required.and(maxlen(TEXT_MAX_LEN)),
            signer_position: required.and(maxlen(STRING_MAX_LEN)),
            edrpou: required.and(edrpou),
            bank_account: required.and(iban),
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/clinics/money-recievers',
            fetch: '/api/v1/clinics/money-recievers/{id}',
            update: '/api/v1/clinics/money-recievers/{id}',
            delete: '/api/v1/clinics/money-recievers/{id}',
        }
    }
}

export default MoneyReciever;
