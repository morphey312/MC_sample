import BaseRepository from '@/repositories/base-repository';
import InformationSource from '@/models/patient/information-source';

class InformationSourceRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/patients/information-sources';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new InformationSource(row);
    }
}

export default InformationSourceRepository;