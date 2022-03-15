import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN
} from '@/services/validation';

/**
 * Laboratory model
 */
class Laboratory extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Analysis_Laboratory';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: '',
            disabled: false,
            external_id: null,
            clinics: []
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
            create: '/api/v1/analysis/laboratories',
            fetch: '/api/v1/analysis/laboratories/{id}',
            update: '/api/v1/analysis/laboratories/{id}',
            delete: '/api/v1/analysis/laboratories/{id}',
        }
    }
}

export default Laboratory;
