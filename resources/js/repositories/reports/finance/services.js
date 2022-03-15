import BaseReportRepository from '../base-report-repository';

class ServicesReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/finance/services');
        this.analysesEndpoint = 'analysis-results';
    }

    /**
     * Fetch data
     *
     * @param {object} filters
     *
     * @returns {Promise}
     */
    fetch(filters = null) {
        return super.fetch(filters).then((services) => {
            return this.fetchInternal(this.buildUrl(this.analysesEndpoint, {
                ...this.getFilters(filters), 
            }), false).then((analyses) => {
                return {services, analyses};
            });
        });
    }
}

export default ServicesReportRepository;
