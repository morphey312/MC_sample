import BaseRepository from '@/repositories/base-repository';
import AmbulanceCall from '@/models/ambulance-call';

class AmbulanceCallRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/appointments/ambulance-calls';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new AmbulanceCall(row);
    }
}

export default AmbulanceCallRepository;
