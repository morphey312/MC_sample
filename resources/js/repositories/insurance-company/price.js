import BaseRepository from '@/repositories/base-repository';
import Price from '@/models/price';

class PriceRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/insurance/prices';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Price(row);
    }
}

export default PriceRepository;