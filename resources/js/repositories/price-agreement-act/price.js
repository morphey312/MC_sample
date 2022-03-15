import BaseRepository from '@/repositories/base-repository';
import Price from '@/models/price-agreement-act/price';

class PriceRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'name', direction: 'asc'}],
            ...options,
        });
        this.endpoint = '/api/v1/price-agreement-act/price';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Price(row);
    }
}

export default PriceRepository;
