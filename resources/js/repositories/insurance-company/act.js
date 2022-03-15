import BaseRepository from '@/repositories/base-repository';
import InsuranceCompanyAct from '@/models/insurance-company/act';

class InsuranceCompanyActRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/insurance/company-acts';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new InsuranceCompanyAct(row);
    }
}

export default InsuranceCompanyActRepository;