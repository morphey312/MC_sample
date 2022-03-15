import BaseRepository from '@/repositories/base-repository';
import CallRequestPurpose from '@/models/call-request/purpose';

class CallRequestPurposeRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/call-requests/purposes';
    }

    /**
    * @inheritdoc
    */
    transformRow(row) {
        return new CallRequestPurpose(row);
    }
}

export default CallRequestPurposeRepository;