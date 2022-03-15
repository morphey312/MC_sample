import BaseModel from '@/models/base-model';
import {
    required,
    requiredArray,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

class ClinicRoute extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            file_id: null,
            specializations: [],
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            file_id: required,
            specializations: requiredArray,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/clinic-routes',
            fetch: '/api/v1/patients/clinic-routes/{id}',
            update: '/api/v1/patients/clinic-routes/{id}',
            delete: '/api/v1/patients/clinic-routes/{id}',
        }
    }
}

export default ClinicRoute;
