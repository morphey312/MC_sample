import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

class CallRequestPurpose extends BaseModel 
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'CallRequest_Purpose';
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
            auto_add: false,
            manual_add: false,
            auto_next_visit: false,
            call_results: [],
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
            create: '/api/v1/call-requests/purposes',
            fetch: '/api/v1/call-requests/purposes/{id}',
            update: '/api/v1/call-requests/purposes/{id}',
            delete: '/api/v1/call-requests/purposes/{id}',
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

export default CallRequestPurpose;
