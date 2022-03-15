import BaseModel from '@/models/base-model';

/**
 * PriceSet model
 */
class Set extends BaseModel
{
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            type: '',
            owner_id: null,
            owner_type: null,
        }
    }
}

export default Set;