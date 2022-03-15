import BaseRepository from '@/repositories/base-repository';
import LabSchedule from '@/models/lab-schedule';

class LaboratoriesScheduleRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/lab-schedule';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new LabSchedule(row);
    }
}

export default LaboratoriesScheduleRepository;
