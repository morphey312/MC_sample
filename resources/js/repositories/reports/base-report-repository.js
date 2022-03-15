import BaseRepository from '@/repositories/base-repository';

class BaseReportRepository extends BaseRepository
{
    constructor(endpoint) {
        super({});
        this.endpoint = endpoint;
    }

    /**
     * Fetch data
     *
     * @param {object} filters
     *
     * @returns {Promise}
     */
    fetch(filters = null) {
        return this.fetchInternal(this.buildUrl(null, {
            ...this.getFilters(filters), 
        }), false);
    }
}

export default BaseReportRepository;