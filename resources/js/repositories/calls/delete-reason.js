import BaseRepository from '@/repositories/base-repository';
import CallDeleteReason from '@/models/calls/delete-reason';

class CallDeleteReasonRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            ...options,
        });
        this.endpoint = '/api/v1/calls/delete-reasons';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new CallDeleteReason(row);
    }
}

export default CallDeleteReasonRepository;