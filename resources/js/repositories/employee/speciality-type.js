import BaseRepository from '@/repositories/base-repository';
import EmployeeSpecialityType from '@/models/employee/speciality-type';

class EmployeeSpecialityTypeRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/employees/speciality-types';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new EmployeeSpecialityType(row);
    }
}

export default EmployeeSpecialityTypeRepository;