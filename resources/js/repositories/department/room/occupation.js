import BaseRepository from '@/repositories/base-repository';
import Occupation from '@/models/department/room/occupation';

class OccupationRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/departments/rooms/occupations';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new Occupation(row);
    }
}

export default OccupationRepository;