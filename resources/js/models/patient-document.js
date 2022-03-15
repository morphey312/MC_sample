import BaseModel from '@/models/base-model';

import {
    required,
    requiredArray,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

class PatientDocument extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            name_ua: null,
            file_id: null,
            clinic_id: null,
            specializations: [],
            is_official_form: false,
            is_conclusion: false,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            name_ua: required.and(maxlen(STRING_MAX_LEN)),
            file_id: required,
            clinic_id: required,
            specializations: requiredArray,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patient-documents',
            fetch: '/api/v1/patient-documents/{id}',
            update: '/api/v1/patient-documents/{id}',
            delete: '/api/v1/patient-documents/{id}',
        }
    }
}

export default PatientDocument;
