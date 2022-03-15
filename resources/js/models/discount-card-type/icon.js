import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * Icon model
 */
class Icon extends BaseModel
{
	/** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: '',
            attachments: [],
            attachments_data: [],
        }
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/discount-card-types/icons',
            fetch: '/api/v1/discount-card-types/icons/{id}',
            update: '/api/v1/discount-card-types/icons/{id}',
            delete: '/api/v1/discount-card-types/icons/{id}',
        }
    }
}

export default Icon;