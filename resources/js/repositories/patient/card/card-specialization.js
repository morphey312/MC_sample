import BaseRepository from '@/repositories/base-repository';
import CardSpecialization from '@/models/patient/card/card-specialization';

class CardSpecializationRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/cards/specializations';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new CardSpecialization(row);
    }
}

export default CardSpecializationRepository;