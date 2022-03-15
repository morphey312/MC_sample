import BaseModel from '@/models/base-model';

import {
    maxlen,
    required,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
} from '@/services/validation';

/**
 * Clinic blank model
 */
class ClinicGroup extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
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
            create: '/api/v1/clinics/groups',
            fetch: '/api/v1/clinics/groups/{id}',
            update: '/api/v1/clinics/groups/{id}',
            delete: '/api/v1/clinics/groups/{id}',
        }
    }
}

export default ClinicGroup;