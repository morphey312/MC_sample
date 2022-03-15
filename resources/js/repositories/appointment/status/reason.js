import BaseRepository from '@/repositories/base-repository';
import Reason from '@/models/appointment/status/reason';

class ReasonRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [ {field: 'name', direction: 'asc'} ],
            ...options,
        });
        this.endpoint = '/api/v1/appointments/statuses/reasons';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Reason(row);
    }
}

export default ReasonRepository;
