import BaseRepository from '@/repositories/base-repository';
import AppointmentDelay from '@/models/appointment/delay';

class AppointmentDelayRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/appointments/delays';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new AppointmentDelay(row);
    }
}

export default AppointmentDelayRepository;