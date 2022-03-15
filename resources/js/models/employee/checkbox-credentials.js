import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    TEXT_MAX_LEN
} from '@/services/validation';

class EmployeeCheckboxCredentials extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            employee_id: null,
            money_reciever_cashbox_id: null,
            login: null,
            password: null,
            money_reciever_id: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            employee_id: required,
            money_reciever_cashbox_id: required,
            login: required,
            password: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/checkbox-credentials',
            fetch: '/api/v1/employees/checkbox-credentials/{id}',
            update: '/api/v1/employees/checkbox-credentials/{id}',
            delete: '/api/v1/employees/checkbox-credentials/{id}',
        }
    }
}

export default EmployeeCheckboxCredentials;
