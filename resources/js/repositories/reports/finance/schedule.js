import BaseReportRepository from '../base-report-repository';

class ScheduleReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/finance/schedule');
    }
}

export default ScheduleReportRepository;