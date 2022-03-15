import BaseRepository from '@/repositories/base-repository';
import LockLog from '@/models/locklog';

class LockLogRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/locklogs';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new LockLog(row);
    }
}

export default LockLogRepository;
