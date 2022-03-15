import BaseRepository from '@/repositories/base-repository';
import InsuranceCompany from '@/models/insurance-company';

class InsuranceCompanyRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'short_name'}],
            ...options,
        });
        this.endpoint = '/api/v1/insurance-companies';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new InsuranceCompany(row);
    }
}

export default InsuranceCompanyRepository;
