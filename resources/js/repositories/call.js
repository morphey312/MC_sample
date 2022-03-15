import BaseRepository from '@/repositories/base-repository';
import Call from '@/models/call';

class CallRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/calls';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Call(row);
    }
}

export default CallRepository;