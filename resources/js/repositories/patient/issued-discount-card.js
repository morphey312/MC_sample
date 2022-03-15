import BaseRepository from '@/repositories/base-repository';
import IssuedDiscountCard from '@/models/patient/issued-discount-card';

class IssuedDiscountCardRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/issued-discount-cards';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new IssuedDiscountCard(row);
    }
}

export default IssuedDiscountCardRepository;