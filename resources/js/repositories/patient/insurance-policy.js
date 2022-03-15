import BaseRepository from '@/repositories/base-repository';
import InsurancePolicy from '@/models/patient/insurance-policy';

class InsurancePolicyRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/insurance-policies';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new InsurancePolicy(row);
    }
}

export default InsurancePolicyRepository;