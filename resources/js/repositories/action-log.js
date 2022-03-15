import BaseRepository from '@/repositories/base-repository';
import ActionLog from '@/models/action-log';

class ActionLogRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/action-logs';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new ActionLog(row);
    }
}

export default ActionLogRepository;