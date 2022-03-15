import BaseModel from '@/models/base-model';
import DepartmentRoom from '@/models/department/room';
import {
    maxlen,
    required,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * Department model
 */
class Department extends BaseModel 
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Department';
    }
    
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: '',
            clinic_id: null,
            type: null,
        }
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            clinic_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            rooms: (value) => _.isArray(value) ? value.map((room) => this.initSubModel(DepartmentRoom, room)) : [],
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/departments',
            fetch: '/api/v1/departments/{id}',
            update: '/api/v1/departments/{id}',
            delete: '/api/v1/departments/{id}',
        }
    }
}

export default Department;
