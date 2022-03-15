import BaseRepository from '@/repositories/base-repository';
import Procedure from '@/models/ehealth/encounter/procedure';

class ProceduretRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/ehealth/encounter/procedures';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Procedure(row);
    }
}

export default ProceduretRepository;
