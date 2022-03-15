import BaseModel from '@/models/base-model';

class EhealthService extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            code: null,
            ehealth_id: null,
        };
    }
}

export default EhealthService;