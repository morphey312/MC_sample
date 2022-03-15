import BaseModel from '@/models/base-model';

import {
    required,
    requiredArray,
    maxlen,
    STRING_MAX_LEN
} from '@/services/validation';

class Role extends BaseModel 
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Role';
    }
    
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            permissions: [],
            users: [],
        }
    }
    
    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            permissions: requiredArray,
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/roles',
            fetch: '/api/v1/roles/{id}',
            update: '/api/v1/roles/{id}',
            delete: '/api/v1/roles/{id}',
        }
    }
}

export default Role;