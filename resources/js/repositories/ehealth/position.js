import BaseRepository from '@/repositories/base-repository';
import EhealthPosition from '@/models/ehealth/position';

class EhealthPositionRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/ehealth/positions';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new EhealthPosition(row);
    }
}

export default EhealthPositionRepository;