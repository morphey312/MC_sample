import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    length,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * Country model
 */
class Country extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Country';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            code: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            code: required.and(length(2, 2)),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/countries',
            fetch: '/api/v1/countries/{id}',
            update: '/api/v1/countries/{id}',
            delete: '/api/v1/countries/{id}',
        }
    }
}

export default Country;
