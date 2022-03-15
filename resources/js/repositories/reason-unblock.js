import BaseRepository from '@/repositories/base-repository';
import ReasonUnblock from '@/models/reasonunblock';

class ReasonUnblockRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/reason-unblock';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new ReasonUnblock(row);
    }
}

export default ReasonUnblockRepository;
