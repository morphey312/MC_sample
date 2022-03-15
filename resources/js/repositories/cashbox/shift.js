import BaseRepository from '@/repositories/base-repository';
import Shift from '@/models/checkbox/shift';

class ShiftRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/checkbox/shifts';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Shift(row);
    }
}

export default ShiftRepository;