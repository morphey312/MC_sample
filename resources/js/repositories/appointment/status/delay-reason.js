import BaseRepository from '@/repositories/base-repository';
import DelayReason from '@/models/appointment/status/delay-reason';

class DelayReasonRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [ {field: 'name', direction: 'asc'} ],
            ...options,
        });
        this.endpoint = '/api/v1/appointments/statuses/delay-reasons';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new DelayReason(row);
    }
}

export default DelayReasonRepository;
