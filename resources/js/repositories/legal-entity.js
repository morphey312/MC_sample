import BaseRepository from '@/repositories/base-repository';
import LegalEntity from '@/models/legal-entity';

class LegalEntityRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/legal-entities';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new LegalEntity(row);
    }
}

export default LegalEntityRepository;