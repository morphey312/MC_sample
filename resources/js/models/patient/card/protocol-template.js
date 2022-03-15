import BaseModel from '@/models/base-model';

import {
    required,
    requiredArray,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

class ProtocolTemplate extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Patient_Card_ProtocolTemplate';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            file_id: null,
            specialization_id: null,
            clinics: [],
            services: [],
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            file_id: required,
            specialization_id: required,
            clinics: requiredArray,
            services: requiredArray,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/protocol-templates',
            fetch: '/api/v1/patients/cards/protocol-templates/{id}',
            update: '/api/v1/patients/cards/protocol-templates/{id}',
            delete: '/api/v1/patients/cards/protocol-templates/{id}',
        }
    }
}

export default ProtocolTemplate;
