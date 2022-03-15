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
            item_id: null,
            name: null,
            random: null,
            barcode: null,
            measure: null,
            image_data: null,
            biomaterial: null,
            container_id: null,
            biomaterial_id: null,
            handbookMeasure: null,
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
