import BaseRepository from '@/repositories/base-repository';
import AppointmentLimitation from '@/models/appointment/limitation';

class AppointmentLimitationRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/appointments/limitations';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new AppointmentLimitation(row);
    }
}

export default AppointmentLimitationRepository;