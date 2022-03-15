import BaseReportRepository from '../base-report-repository';

class DebitorsReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/finance/debitors');
    }
}

export default DebitorsReportRepository;