import BaseRepository from '@/repositories/base-repository';
import CallRequest from '@/models/call-request';

class CallRequestRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/call-requests';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new CallRequest(row);
    }
}

export default CallRequestRepository;