import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN
} from '@/services/validation';

class EmployeeEducation extends BaseModel 
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
            institution_name: null,
            issued_date: null,
            diploma_number: null,
            degree: null,
            speciality: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            country_id: required,
            city: required.and(maxlen(STRING_MAX_LEN)),
            institution_name: required.and(maxlen(STRING_MAX_LEN)),
            diploma_number: required.and(maxlen(STRING_MAX_LEN)),
            degree: required,
            speciality: required.and(maxlen(STRING_MAX_LEN)),
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/educations',
            fetch: '/api/v1/employees/educations/{id}',
            update: '/api/v1/employees/educations/{id}',
            delete: '/api/v1/employees/educations/{id}',
        }
    }
}

export default EmployeeEducation;