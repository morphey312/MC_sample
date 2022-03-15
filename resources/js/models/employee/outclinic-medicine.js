import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

class OutclinicMedicine extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            is_deleted: false,
            comment: null,
            medication_duration: 1,
            quantity: 1,
            doctor_id: null,
            is_apteka24: false,
            apteka24_id: null,
            apteka24_order_id: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            doctor_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            update: 'api/v1/employees/outclinic-medicine/{id}',
        }
    }
}

export default OutclinicMedicine;
