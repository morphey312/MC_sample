import BaseReportRepository from '../base-report-repository';

class IncomeReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/finance/income');
    }

    /**
     * Fetch appointments data
     *
     * @param {object} filters
     *
     * @returns {Promise}
     */
    fetchAppointments(filters = null) {
        return this.fetchInternal(this.buildUrl('appointments', {
            ...this.getFilters(filters), 
        }), false);
    }
}

export default IncomeReportRepository;