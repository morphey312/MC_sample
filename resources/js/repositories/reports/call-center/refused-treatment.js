import BaseReportRepository from '../base-report-repository';

class RefusedTreatmentReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/call-center/refused-treatment');
    }
}

export default RefusedTreatmentReportRepository;