import BaseRepository from '@/repositories/base-repository';
import MedicineStore from '@/models/medicine-store';

class MedicineStoreRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/medicine-stores';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new MedicineStore(row);
    }
}

export default MedicineStoreRepository;