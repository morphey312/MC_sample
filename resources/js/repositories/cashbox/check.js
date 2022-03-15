import BaseRepository from '@/repositories/base-repository';
import Check from '@/models/checkbox/check';

class CheckRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/checkbox/checks';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Check(row);
    }
}

export default CheckRepository;
