import BaseRepository from '@/repositories/base-repository';
import ProcessLog from '@/models/calls/process-log';

class ProcessLogRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/calls/process-logs';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new ProcessLog(row);
    }
}

export default ProcessLogRepository;