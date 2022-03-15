import BaseModel from '@/models/base-model';
import {
    required,
    numeric
} from '@/services/validation';

class Prepayment extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            amount: 0,
            patient_id: null,
            clinic_id: null,
            service_id: null,
            specialization_id: null,
            payment_id: null,
            money_reciever_cashbox_id: null,
            checkbox_money_reciever_id: null,
            used: false,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            amount: numeric,
            patient_id: required,
            clinic_id: required,
            service_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/prepayments',
            fetch: '/api/v1/patients/prepayments/{id}',
            update: '/api/v1/patients/prepayments/{id}',
        }
    }

    /**
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        if (this.cashbox_id) {
            attributes.cashbox_id = this.cashbox_id;
        }
        if (this.cashier_id) {
            attributes.cashier_id = this.cashier_id;
        }
        if (this.comment) {
            attributes.comment = this.comment;
        }
        return attributes;
    }
}

export default Prepayment;
