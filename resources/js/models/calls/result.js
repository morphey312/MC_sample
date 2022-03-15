import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * CallResult model
 */
class CallResult extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Call_Result';
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
            use_for_new_calls: true,
            use_for_statistics: false,
            use_for_is_first_patient: false,
            use_for_repeated_patient: false,
            use_for_unspecialized_patient: false,
            use_for_not_patient: false,
            esputnik_no_answer: false,
            for_wait_list: false,
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
            create: '/api/v1/calls/results',
            fetch: '/api/v1/calls/results/{id}',
            update: '/api/v1/calls/results/{id}',
            delete: '/api/v1/calls/results/{id}',
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

export default CallResult;
