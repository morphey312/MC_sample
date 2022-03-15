import BaseModel from '@/models/base-model';
import {
    required,
    requiredArray,
    assertion,
    numeric,
    gte,
    lte,
    missing,
} from '@/services/validation';

class MoneyRecieverCashbox extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            money_reciever_id: null,
            cashbox_key: null,
            name: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            money_reciever_id: required,
            cashbox_key: required,
            name: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/clinics/money-recievers/cashboxes',
            fetch: '/api/v1/clinics/money-recievers/cashboxes/{id}',
            update: '/api/v1/clinics/money-recievers/cashboxes/{id}',
            delete: '/api/v1/clinics/money-recievers/cashboxes/{id}',
        }
    }
}

export default MoneyRecieverCashbox;
