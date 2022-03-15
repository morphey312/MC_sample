import BaseRepository from '@/repositories/base-repository';
import MedicineFirm from '@/models/medicine-firm';

class MedicineFirmRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/medicine-firms';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new MedicineFirm(row);
    }
}

export default MedicineFirmRepository;
