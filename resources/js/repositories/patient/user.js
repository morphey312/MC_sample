import BaseRepository from '@/repositories/base-repository';
import PatientUser from '@/models/patient/user';

class PatientUserRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/users';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new PatientUser(row);
    }
}

export default PatientUserRepository;