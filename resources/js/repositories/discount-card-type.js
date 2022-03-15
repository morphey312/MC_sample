import BaseRepository from '@/repositories/base-repository';
import DiscountCardType from '@/models/discount-card-type';

class DiscountCardTypeRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/discount-card-types';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new DiscountCardType(row);
    }
}

export default DiscountCardTypeRepository;