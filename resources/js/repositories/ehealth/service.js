import BaseRepository from '@/repositories/base-repository';
import EhealthService from '@/models/ehealth/service';

class EhealthServiceRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/ehealth/services';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new EhealthService(row);
    }
}

export default EhealthServiceRepository;