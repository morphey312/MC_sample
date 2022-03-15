import BaseModel from '@/models/base-model';
import {
    maxlen,
    required,
    STRING_MAX_LEN,
} from '@/services/validation';

class Place extends BaseModel {

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            number: 1,
            status: 1,
            room_id: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            number: maxlen(STRING_MAX_LEN),
            room_id: required,
        };
    }
}

export default Place;