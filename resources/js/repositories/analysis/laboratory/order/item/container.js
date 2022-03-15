import BaseRepository from '@/repositories/base-repository';
import Container from '@/models/analysis/laboratory/order/item/container';

class ContainerRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'created', direction: 'desc'}],
            ...options
        });
        this.endpoint = '/api/v1/analysis/laboratories/orders/items/containers';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Container(row);
    }
}

export default ContainerRepository;
