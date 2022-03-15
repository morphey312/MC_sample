import BaseModel from '@/models/base-model';

class RelatedAction extends BaseModel 
{
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            action: null,
            time: null,
            related_id: null,
            related_type: null,
        };
    }
}

export default RelatedAction;