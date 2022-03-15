import BaseModel from '@/models/base-model';

class EhealthApplication extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            status: null,
            subject_type: null,
            subject_id: null,
            action: null,
            request: null,
            request_data: null,
            response: null,
        };
    }
}

export default EhealthApplication;