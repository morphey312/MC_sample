import BaseRepository from '@/repositories/base-repository';
import DoctorIncomePlan from '@/models/employee/doctor-income-plan';

class DoctorIncomePlanRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/employees/doctor-income-plans';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new DoctorIncomePlan(row);
    }
}

export default DoctorIncomePlanRepository;