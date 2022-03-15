import BaseModel from '@/models/base-model';

/**
 * MedicineFirm model
 */
class MedicineFirm extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            firm_uid: '',
            code: '',
            description: '',
        }
    }
}

export default MedicineFirm;
