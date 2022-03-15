import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    TEXT_MAX_LEN
} from '@/services/validation';

class EmployeeDocument extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            employee_id: null,
            type: null,
            number: null,
            issued_by: null,
            issued_at: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            type: required,
            number: required.and(maxlen(STRING_MAX_LEN)),
            issued_by: maxlen(TEXT_MAX_LEN),
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/documents',
            fetch: '/api/v1/employees/documents/{id}',
            update: '/api/v1/employees/documents/{id}',
            delete: '/api/v1/employees/documents/{id}',
        }
    }
}

export default EmployeeDocument;