import BaseRepository from '@/repositories/base-repository';
import CallResult from '@/models/calls/result';

class CallResultRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}], 
            ...options,
        });
        this.endpoint = '/api/v1/calls/results';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new CallResult(row);
    }
}

export default CallResultRepository;