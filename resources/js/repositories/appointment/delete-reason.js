import BaseRepository from '@/repositories/base-repository';
import AppointmentDeleteReason from '@/models/appointment/delete-reason';

class AppointmentDeleteReasonRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/appointments/delete-reasons';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new AppointmentDeleteReason(row);
    }
}

export default AppointmentDeleteReasonRepository;