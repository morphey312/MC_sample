import BaseRepository from '@/repositories/base-repository';
import IssuedMedicine from '@/models/patient/issued-medicine';

class IssuedMedicineRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/issued-medicines';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new IssuedMedicine(row);
    }
}

export default IssuedMedicineRepository;