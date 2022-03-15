import BaseRepository from '@/repositories/base-repository';
import BonusNorm from '@/models/clinic/bonus-norm';

class BonusNormRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/bonus-norms';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new BonusNorm(row);
    }
}

export default BonusNormRepository;