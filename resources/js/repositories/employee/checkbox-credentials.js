import BaseRepository from '@/repositories/base-repository';
import EmployeeCheckboxCredentials from "@/models/employee/checkbox-credentials";

class CheckboxCredentialsRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/employees/checkbox-credentials';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new EmployeeCheckboxCredentials(row);
    }
}

export default CheckboxCredentialsRepository;
