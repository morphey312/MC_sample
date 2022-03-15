import BaseModel from '@/models/base-model';

import {
    required,
    numeric,
    assertion,
} from '@/services/validation';

class CashTransfer extends BaseModel {

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            source_id: null,
            destination_id: null,
            cashier_id: null,
            amount: 0,
            comment: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            cashier_id: required,
            amount: numeric,
            source_id: assertion(() => _.isFilled(this.destination_id)).or(required),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/cashbox/cash-transfers',
        }
    }
}

export default CashTransfer;
