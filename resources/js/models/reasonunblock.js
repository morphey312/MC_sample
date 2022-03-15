import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * Country model
 */
class ReasonUnblock extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'ReasonUnblock';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            name_lc1: null,
            name_lc2: null,
            name_lc3: null,
            for_operation: false,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            name_lc1: maxlen(STRING_MAX_LEN),
            name_lc2: maxlen(STRING_MAX_LEN),
            name_lc3: maxlen(STRING_MAX_LEN),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/reason-unblock',
            fetch: '/api/v1/reason-unblock/{id}',
            update: '/api/v1/reason-unblock/{id}',
            delete: '/api/v1/reason-unblock/{id}',
        }
    }

    /**
     * Get localized name
     *
     * @returns {String}
     */
    get name_i18n() {
        return this.getAttributeI18N('name');
    }
}

export default ReasonUnblock;
