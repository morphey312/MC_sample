import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    attributeEquals,
    numeric,
} from '@/services/validation';
import CONSTANTS from '@/constants';

/**
 * Payment model
 */
class Payment extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Payment';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            service_id: null,
            amount: 0,
            payed_amount: 0,
            discount: 0,
            cashbox_id: null,
            clinic_id: null,
            money_reciever_id: null,
            doctor_id: null,
            cashier_id: null,
            patient_id: null,
            appointment_id: null,
            payment_destination_id: null,
            type: null,
            is_technical: false,
            is_prepayment: false,
            is_deposit: false,
            is_cash: true,
            from_deposit: false,
            is_deleted: false,
            timestamp: null,
            comment: null,
            check_id: null,
            doctor: null,
            appointment: null,
            cashbox: null,
            service: null,
            check: null,
            created_at: null,
            checkbox_money_reciever_id: null,
            money_reciever_cashbox_id: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            amount: numeric,
            payed_amount: numeric,
            service_id: required.or(attributeEquals('is_deposit', true)),
            cashbox_id: required,
            clinic_id: required,
            doctor_id: required.or(attributeEquals('is_deposit', true)),
            cashier_id: required,
            patient_id: required,
            payment_destination_id: required.or(attributeEquals('is_deposit', true).or(attributeEquals('type', CONSTANTS.PAYMENT.TYPES.EXPENSE))),
            type: required.and(maxlen(STRING_MAX_LEN)),
            is_deposit: required,
            is_deleted: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/payments',
            fetch: '/api/v1/payments/{id}',
            update: '/api/v1/payments/{id}',
            delete: '/api/v1/payments/{id}',
            updateService: '/api/v1/payments/{id}/update-service',
        }
    }

    /**
     * Update services in payment
     *
     * @param {object} attributes
     *
     * @returns Promise
     */
    updateService(attributes) {
        let route  = this.getRoute('updateService');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let method = this.getUpdateMethod();
        let data   = attributes;

        return this.getRequest({method, url, data}).send().then((result) => {
            return Promise.resolve();
        });
    }

    /**
     * Verify entity cashbox has fiscal payment in clinic
     *
     * @returns {bool}
     */
    get isFiscal() {
        if (this.cashbox) {
            let clinics = _.get(this, 'cashbox.payment_method.clinics');
            let clinic = clinics.find(item => item.clinic_id == this.clinic_id)
            return clinic ? clinic.is_fiscal : true;
        }
        return true;
    }

    /**
     * Get patient card number
     *
     * @returns {bool}
     */
    get card_number() {
        return this.attributes.card_number;
    }
}

export default Payment;
