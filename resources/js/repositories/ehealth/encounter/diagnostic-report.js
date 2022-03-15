import BaseRepository from '@/repositories/base-repository';
import DiagnosticReport from '@/models/ehealth/encounter/diagnostic-report';

class DiagnosticReportRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/ehealth/encounter/diagnostic-reports';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new DiagnosticReport(row);
    }
}

export default DiagnosticReportRepository;
