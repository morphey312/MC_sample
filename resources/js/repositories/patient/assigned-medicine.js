import BaseRepository from '@/repositories/base-repository';
import AssignedMedicine from '@/models/patient/assigned-medicine';

class AssignedMedicineRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/assigned-medicines';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new AssignedMedicine(row);
    }
}

export default AssignedMedicineRepository;