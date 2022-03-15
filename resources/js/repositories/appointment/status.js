import BaseRepository from '@/repositories/base-repository';
import AppointmentStatus from '@/models/appointment/status';

class AppointmentStatusRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/appointments/statuses';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new AppointmentStatus(row);
    }
}

export default AppointmentStatusRepository;