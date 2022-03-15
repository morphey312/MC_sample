import BaseReportRepository from '../base-report-repository';

class OperatorsReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/call-center/operators');
    }
}

export default OperatorsReportRepository;