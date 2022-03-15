import BaseRecord from './base-record';
import {
    required,
    maxlen,
    TEXT_MAX_LEN
} from '@/services/validation';

class ServiceRecord extends BaseRecord
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            card_specialization_id: null,
            appointment_id: null,
            comment: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            comment: required.and(maxlen(TEXT_MAX_LEN)),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/service-records',
            fetch: '/api/v1/patients/cards/service-records/{id}',
            update: '/api/v1/patients/cards/service-records/{id}',
            delete: '/api/v1/patients/cards/service-records/{id}',
        };
    }
}

export default ServiceRecord;
