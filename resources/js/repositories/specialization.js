import BaseRepository from '@/repositories/base-repository';
import Specialization from '@/models/specialization';

class SpecializationRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name_i18n'}],
            ...options,
        });
        this.endpoint = '/api/v1/specializations';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Specialization(row);
    }
}

export default SpecializationRepository;