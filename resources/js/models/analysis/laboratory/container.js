import BaseModel from '@/models/base-model';

/**
 * Laboratory order item model
 */
class Container extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            container_id: null,
            biomaterial_id: null,
            patient_id: null,
            barcode: null,
            results: [],
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            fetch: '/api/v1/analysis/laboratories/orders/items/containers/{id}',
        }
    }
}

export default Container;
