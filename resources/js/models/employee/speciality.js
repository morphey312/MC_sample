import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN
} from '@/services/validation';

class EmployeeSpeciality extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            employee_id: null,
            speciality_id: null,
            primary: false,
            level: null,
            qualification_type: null,
            attestation_name: null,
            attestation_date: null,
            valid_to_date: null,
            certificate_number: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            speciality_id: required,
            level: required,
            qualification_type: required,
            attestation_name: required.and(maxlen(STRING_MAX_LEN)),
            attestation_date: required,
            certificate_number: required.and(maxlen(STRING_MAX_LEN)),
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/specialities',
            fetch: '/api/v1/employees/specialities/{id}',
            update: '/api/v1/employees/specialities/{id}',
            delete: '/api/v1/employees/specialities/{id}',
        }
    }
}

export default EmployeeSpeciality;