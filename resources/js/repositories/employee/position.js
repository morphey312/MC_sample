import BaseRepository from '@/repositories/base-repository';
import Position from '@/models/employee/position';

class PositionRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/employees/positions';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Position(row);
    }
}

export default PositionRepository;