import BaseRepository from '@/repositories/base-repository';
import WaitListRecord from '@/models/wait-list-record';

class WaitListRecordRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/wait-list-records';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new WaitListRecord(row);
    }
}

export default WaitListRecordRepository;