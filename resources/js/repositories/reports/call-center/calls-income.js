import BaseReportRepository from '../base-report-repository';

class CallsIncomeReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/call-center/calls-income');
    }
}

export default CallsIncomeReportRepository;