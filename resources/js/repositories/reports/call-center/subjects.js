import BaseReportRepository from '../base-report-repository';

class SubjectsReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/call-center/subjects');
    }
}

export default SubjectsReportRepository;