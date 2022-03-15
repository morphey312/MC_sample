import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';

/**
 * Laboratory order model
 */
class Order extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            status: null,
            number: null,
            transfer_id: null,
            clinic_id: null,
            appointment_id: null,
            comment: null,
            items: [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            clinic_id: required,
            appointment_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/analysis/laboratories/orders',
            fetch: '/api/v1/analysis/laboratories/orders/{id}',
            update: '/api/v1/analysis/laboratories/orders/{id}',
            delete: '/api/v1/analysis/laboratories/orders/{id}',
        }
    }

    /**
     * Get patient(s) name
     * 
     * @returns {string}
     */
    get patient_name() {
        if (this.patient) {
            return this.patient.full_name;
        }
        if (this.items) {
            return this.items.map(item => {
                return item.patient_name;
            })
            .join(', ');
        }
        return '';
    }
}

export default Order;
