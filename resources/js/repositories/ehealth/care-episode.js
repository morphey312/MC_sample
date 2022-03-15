import BaseRepository from '@/repositories/base-repository';
import CareEpisode from '@/models/ehealth/care-episode';

class CareEpisodeRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/ehealth/care-episodes';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new CareEpisode(row);
    }
}

export default CareEpisodeRepository;
