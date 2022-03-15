import BaseModel from '@/models/base-model';

class EhealthPosition extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            code: null,
            is_owner: null,
        };
    }
}

export default EhealthPosition;