import BaseModel from '@/models/base-model';

import {
    required,
} from '@/services/validation';

class RecordTemplate extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            template: null,
        }
    }
}

export default RecordTemplate;