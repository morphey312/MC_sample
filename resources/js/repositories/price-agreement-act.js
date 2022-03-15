import BaseRepository from '@/repositories/base-repository';
import PriceAgreementAct from '@/models/price-agreement-act';
import CONSTANTS from '@/constants';

class PriceAgreementActRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'name', direction: 'asc'}],
            ...options,
        });
        this.endpoint = '/api/v1/price-agreement-act';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new PriceAgreementAct(row);
    }
}

export default PriceAgreementActRepository;
