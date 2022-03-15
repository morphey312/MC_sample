import BaseRepository from '@/repositories/base-repository';
import ClinicGroup from '@/models/clinic/group';

class ClinicGroupRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/clinics/groups';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new ClinicGroup(row);
    }
}

export default ClinicGroupRepository;
