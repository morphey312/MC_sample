import BaseRepository from '@/repositories/base-repository';
import ExchangeRate from '@/models/exchange-rate';

class ExchangeRateRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'desc', field: 'date'}],
            ...options,
        });
        this.endpoint = '/api/v1/exchange-rates';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new ExchangeRate(row);
    }
}

export default ExchangeRateRepository;