import BaseRepository from '@/repositories/base-repository';
import TimeBlockReason from '@/models/day-sheet/time-block-reason';

class TimeBlockReasonRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/day-sheets/time-block-reasons';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new TimeBlockReason(row);
    }
}

export default TimeBlockReasonRepository;
