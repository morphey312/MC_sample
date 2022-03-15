import BaseReportRepository from '../base-report-repository';

class InsurancePatientsReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/finance');
        this.patientsEndpoint = 'insurance-patients';
        this.paymentsEndpoint = 'insurance-payments';
    }

    /**
     * Fetch data
     *
     * @param {object} filters
     *
     * @returns {Promise}
     */
    fetch(filters = null) {
        return this.fetchPatients(filters).then(appointments => {
            return this.fetchInternal(this.buildUrl(this.paymentsEndpoint, {
                ...this.getFilters(filters),
            }), false).then((payments) => {
                return {appointments, payments};
            });
        });
    }

    /**
     * 
     * @param {object} filters 
     */
    fetchPatients(filters) {
        return this.fetchInternal(this.buildUrl(this.patientsEndpoint, {
            ...this.getFilters(filters), 
        }), false);
    }
}

export default InsurancePatientsReportRepository;