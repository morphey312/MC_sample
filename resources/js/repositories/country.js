import BaseRepository from '@/repositories/base-repository';
import Country from '@/models/country';

class CountryRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/countries';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Country(row);
    }
}

export default CountryRepository;
