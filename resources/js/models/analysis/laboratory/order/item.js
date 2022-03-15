import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';

/**
 * Laboratory order item model
 */
class Item extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            patient_id: null,
            comment: null,
            barcode: null,
            results: [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            results: required,
            patient_id: required,
            barcode: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/analysis/laboratories/orders/items',
        }
    }
}

export default Item;
