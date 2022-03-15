import BaseRepository from '@/repositories/base-repository';
import ServiceType from '@/models/clinic/service-type';

class ServiceTypeRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/clinics/service-types';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new ServiceType(row);
    }
}

export default ServiceTypeRepository;
