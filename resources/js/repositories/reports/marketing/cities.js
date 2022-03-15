import BaseReportRepository from '../base-report-repository';

class MarketingCitiesReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/marketing/cities');
    }
}

export default MarketingCitiesReportRepository;
