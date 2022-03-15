import BaseModel from '@/models/base-model';
import {
    required,
    date,
    assertion,
} from '@/services/validation';

/**
 * PaymentDestination model
 */
class PaymentDestination extends BaseModel
{
	/** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            payment_destination_id: null,
            discount_percent: 0,
            date_start: null,
            date_end: null,
            additional_service_mark: '',
            name: '',
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            id: () => this.getId(),
        }
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            payment_destination_id: required,
            date_start: required.and(date),
            date_end: required.and(date).and(assertion(() => {
                return this.date_end === this.date_start || this.date_end > this.date_start;
            })),
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/discount-card-types/icons',
            fetch: '/api/v1/discount-card-types/icons/{id}',
            update: '/api/v1/discount-card-types/icons/{id}',
            delete: '/api/v1/discount-card-types/icons/{id}',
        }
    }

    getId() {
        return this.payment_destination_id + '-' + 
               this.date_start + '-' +
               this.date_end + '-' +
               this.discount_percent;
    }
}

export default PaymentDestination;