import BaseRepository from '@/repositories/base-repository';
import Item from '@/models/analysis/laboratory/order/item';

class ItemRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'created', direction: 'desc'}],
            ...options
        });
        this.endpoint = '/api/v1/analysis/laboratories/orders/items';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Item(row);
    }
}

export default ItemRepository;