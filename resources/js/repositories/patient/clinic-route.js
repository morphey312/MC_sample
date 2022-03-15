import BaseRepository from '@/repositories/base-repository';
import PatientClinicRoute from '@/models/patient/clinic-route';

class ClinicRouteRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/clinic-routes';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new PatientClinicRoute(row);
    }
}

export default ClinicRouteRepository;