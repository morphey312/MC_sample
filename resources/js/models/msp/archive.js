import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN
} from '@/services/validation';

/**
 * MspArchive model
 */
class MspArchive extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'MspArchive';
    }

    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            date: null,
            place: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            date: required,
            place: required.and(maxlen(STRING_MAX_LEN)),
        };
    }
}

export default MspArchive;