import BaseRepository from '@/repositories/base-repository';
import Condition from '@/models/ehealth/encounter/condition';

class ConditionRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/ehealth/encounter/conditions';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Condition(row);
    }
}

export default ConditionRepository;
