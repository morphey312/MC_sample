import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';

class EmployeeServiceType extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            employee_id: null,
            service_id: null,
            ehealth_id: null,
            ehealth_request: null,
            is_deleted: false,
            start_date: null,
            end_date: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            service_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/service-types',
            fetch: '/api/v1/employees/service-types/{id}',
            update: '/api/v1/employees/service-types/{id}',
            delete: '/api/v1/employees/service-types/{id}',
        }
    }
}

export default EmployeeServiceType;