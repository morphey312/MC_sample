import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';
import CONSTANTS from '@/constants';

class AppointmentDocument extends BaseModel
{

    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'AppointmentDocument';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            assigner_id: null,
            patient_id: null,
            appointment_id: null,
            type: null,
            file_id: null,
            number: null,
            assigner_name: null,
            url: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            assigner_id: required,
            patient_id: required,
            appointment_id: required,
            type: required,
            file_id: required,
            number: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/appointment-document',
            fetch: '/api/v1/patients/appointment-document/{id}',
            update: '/api/v1/patients/appointment-document/{id}',
            delete: '/api/v1/patients/appointment-document/{id}',
        }
    }
}

export default AppointmentDocument;
