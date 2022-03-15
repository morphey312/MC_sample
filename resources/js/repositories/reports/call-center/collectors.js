import BaseReportRepository from '../base-report-repository';

class CollectorsReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/call-center/collectors');
        this.paymentsEndpoint = 'payments';
    }

    /**
     * Fetch data
     *
     * @param {object} filters
     *
     * @returns {Promise}
     */
    fetch(filters = null) {
        return super.fetch(filters).then((collectors) => {
            return this.fetchInternal(this.buildUrl(this.paymentsEndpoint, {
                ...this.getFilters(filters), 
            }), false).then((payments) => {
                return {collectors, payments};
            });
        });
    }
}

export default CollectorsReportRepository;