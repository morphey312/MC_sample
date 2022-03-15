import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    TEXT_MAX_LEN
} from '@/services/validation';

class EmployeeQualification extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            employee_id: null,
            type: null,
            institution_name: null,
            speciality: null,
            issued_date: null,
            certificate_number: null,
            valid_to: null,
            additional_info: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            type: required,
            institution_name: required.and(maxlen(STRING_MAX_LEN)),
            speciality: required.and(maxlen(STRING_MAX_LEN)),
            certificate_number: required.and(maxlen(STRING_MAX_LEN)),
            additional_info: maxlen(TEXT_MAX_LEN),
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/qualifications',
            fetch: '/api/v1/employees/qualifications/{id}',
            update: '/api/v1/employees/qualifications/{id}',
            delete: '/api/v1/employees/qualifications/{id}',
        }
    }
}

export default EmployeeQualification;