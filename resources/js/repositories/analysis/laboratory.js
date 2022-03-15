import BaseRepository from '@/repositories/base-repository';
import Laboratory from '@/models/analysis/laboratory';

class LaboratoryRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'name', direction: 'asc'}],
            ...options
        });
        this.endpoint = '/api/v1/analysis/laboratories';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Laboratory(row);
    }
}

export default LaboratoryRepository;
