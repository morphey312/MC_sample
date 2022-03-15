import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * Notification model
 */
class Notification extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Notification';
    }
    
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
  
        }
    }
}

export default Notification;