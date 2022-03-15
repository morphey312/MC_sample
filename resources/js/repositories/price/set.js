import BaseRepository from '@/repositories/base-repository';
import PriceSet from '@/models/price/set';

class SetRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/prices/sets';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new PriceSet(row);
    }
}

export default SetRepository;