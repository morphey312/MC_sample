import BaseRepository from '@/repositories/base-repository';
import EhealthApplication from '@/models/ehealth/application';

class EhealthApplicationRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/ehealth/applications';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new EhealthApplication(row);
    }
}

export default EhealthApplicationRepository;