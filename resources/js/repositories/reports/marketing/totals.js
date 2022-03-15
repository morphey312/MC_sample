import BaseReportRepository from '../base-report-repository';

class MarketingTotalsReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/marketing/totals');
    }
}

export default MarketingTotalsReportRepository;
