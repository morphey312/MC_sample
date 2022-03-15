import BaseRepository from '@/repositories/base-repository';
import PatientRegistration from '@/models/patient/registration';

class PatientRegistrationRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/registrations';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new PatientRegistration(row);
    }
}

export default PatientRegistrationRepository;