import BaseReportRepository from '../base-report-repository';

class OnlineServiceReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/finance/online-service');
    }
}

export default OnlineServiceReportRepository;