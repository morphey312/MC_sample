import BaseRepository from '@/repositories/base-repository';
import DischargeDepartment from '@/models/ehealth/encounter/handbook/discharge-department';

class EhealthDischargeDepartmentRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/ehealth/encounter/handbook/discharge-department';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new DischargeDepartment(row);
    }
}

export default EhealthDischargeDepartmentRepository;
