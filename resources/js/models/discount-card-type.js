import BaseModel from '@/models/base-model';
import PaymentDestination from '@/models/discount-card-type/payment-destination';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    assertion,
} from '@/services/validation';

/**
 * DiscountCardType model
 */
class DiscountCardType extends BaseModel 
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'DiscountCardType';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: '',
            use_detail_payments: false,
            discount_percent: 0,
            dont_use_for_patient: false,
            show_card_in_patient_list: false,
            type_icon_id: null,
            cant_be_copied: false,
            propose_to_disable_on_copy: false,
            max_owners: 1,
            dont_auto_add_to_appointment: false,
            priority: 1,
            expire_period: 180,
            use_card_number: false,
            number_kind_id: null,
            payment_destinations: [],
            clinics: [],
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            payment_destinations: (value) => _.isArray(value) ? value.map((item) => this.initSubModel(PaymentDestination, item)) : [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            payment_destinations: () => {
                if (this.payment_destinations.length === 0) {
                    return true;
                }
                return this.validateModelsArray(this.payment_destinations);
            },
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/discount-card-types',
            fetch: '/api/v1/discount-card-types/{id}',
            update: '/api/v1/discount-card-types/{id}',
            delete: '/api/v1/discount-card-types/{id}',
        }
    }

    /** 
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        
        if (_.get(this, 'show_card_in_patient_list', false) === false) {
            attributes.type_icon_id = null;
        }
        if (_.get(this, 'use_card_number', false) === false) {
            attributes.number_kind_id = null;
        }
        if (_.get(this, 'cant_be_copied', true) === true) {
            attributes.propose_to_disable_on_copy = false;
            attributes.max_owners = 1;
        }
        if (_.get(this, 'use_detail_payments', true) === true) {
            attributes.discount_percent = 0;
        }
        if (_.get(this, 'use_detail_payments', false) === false) {
            attributes.payment_destinations = [];
        }
        
        return attributes;
    }
}

export default DiscountCardType;
