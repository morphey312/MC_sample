import BaseRepository from '@/repositories/base-repository';
import Msp from '@/models/msp';

class MspRepository extends BaseRepository
{
    constructor(options = {}) {
        super({});
        this.endpoint = '/api/v1/msp';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Msp(row);
    }
}

export default MspRepository;