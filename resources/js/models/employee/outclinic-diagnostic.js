import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

class OutclinicDiagnostic extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            is_deleted: false,
            comment: null,
            doctor_id: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            doctor_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            update: 'api/v1/employees/outclinic-diagnostic/{id}',
        }
    }
}
export default OutclinicDiagnostic;
