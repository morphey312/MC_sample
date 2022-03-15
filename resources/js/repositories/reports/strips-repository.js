import BaseRepository from '@/repositories/base-repository';

class StripsReportRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/call-center';
    }

    /**
     * Fetch data
     *
     * @param {object} filters
     * @param {array} sort
     * @param {array} scopes
     * @param {number} page
     *
     * @returns {Promise}
     */
    fetch(filters = null, sort = null, page = 1, limit = 50) {
        return axios.get(this.buildUrl('strips-report', {filters}))
                .then((response) => {
                return response.data;
        });
    }

}

export default StripsReportRepository;