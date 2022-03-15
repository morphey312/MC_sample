import BaseModel from '@/models/base-model';

/**
 * Clinic blank model
 */
class Blank extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            type: '',
            clinic_id: null,
            attachments: [],
        }
    }

    
}

export default Blank;