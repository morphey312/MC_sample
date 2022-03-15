import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN
} from '@/services/validation';

class EmployeeScienceDegree extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            employee_id: null,
            country_id: null,
            city: null,
            degree: null,
            institution_name: null,
            diploma_number: null,
            speciality: null,
            issued_date: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            country_id: required,
            city: required.and(maxlen(STRING_MAX_LEN)),
            degree: required,
            institution_name: required.and(maxlen(STRING_MAX_LEN)),
            diploma_number: required.and(maxlen(STRING_MAX_LEN)),
            speciality: required.and(maxlen(STRING_MAX_LEN)),
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/science-degrees',
            fetch: '/api/v1/employees/science-degrees/{id}',
            update: '/api/v1/employees/science-degrees/{id}',
            delete: '/api/v1/employees/science-degrees/{id}',
        }
    }
}

export default EmployeeScienceDegree;