import BaseRepository from '@/repositories/base-repository';
import CallLog from '@/models/calls/call-log';

class CallLogRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/calls/call-logs';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new CallLog(row);
    }
}

export default CallLogRepository;