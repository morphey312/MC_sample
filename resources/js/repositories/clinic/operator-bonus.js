import BaseRepository from '@/repositories/base-repository';
import OperatorBonus from '@/models/clinic/operator-bonus';

class OperatorBonusRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/operator-bonuses';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new OperatorBonus(row);
    }
}

export default OperatorBonusRepository;