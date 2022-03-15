import BaseRepository from '@/repositories/base-repository';
import Handbook from '@/models/handbook';

class HandbookRepository extends BaseRepository
{
    /** 
     * @inheritdoc
     */
    constructor(category, options = {}) {
        super(options);
        this.endpoint = '/api/v1/handbook/' + category;
    }

    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new Handbook(row);
    }
}

export default HandbookRepository;