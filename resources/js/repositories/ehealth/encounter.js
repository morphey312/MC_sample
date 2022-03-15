import BaseRepository from '@/repositories/base-repository';
import Encounter from '@/models/ehealth/encounter';

class EncounterRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/ehealth/encounters';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Encounter(row);
    }
}

export default EncounterRepository;
