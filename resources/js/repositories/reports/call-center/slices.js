import BaseReportRepository from '../base-report-repository';

class SlicesReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/call-center/slices');
    }
}

export default SlicesReportRepository;