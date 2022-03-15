import BaseReportRepository from '../base-report-repository';

class RedirectReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/finance');
        this.externalEndpoint = 'redirects-external';
        this.internalEndpoint = 'redirects-internal';
    }

    fetch(filters = null) {
        return this.fetchExternalRedirects(filters).then(external => {
            return this.fetchInternalRedirects(filters).then(internal => {
                return Promise.resolve({external, internal});
            });
        });
    }

    fetchExternalRedirects(filters) {
        return this.fetchInternal(this.buildUrl(this.externalEndpoint, {
            ...this.getFilters(filters), 
        }), false);
    }

    fetchInternalRedirects(filters) {
        return this.fetchInternal(this.buildUrl(this.internalEndpoint, {
            ...this.getFilters(filters), 
        }), false);
    }
}

export default RedirectReportRepository;