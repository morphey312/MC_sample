import BaseRecord from './base-record';
import {
    required,
    maxlen,
    TEXT_MAX_LEN
} from '@/services/validation';

class ManipulationRecord extends BaseRecord
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
            create: '/api/v1/patients/cards/manipulation-records',
            fetch: '/api/v1/patients/cards/manipulation-records/{id}',
            update: '/api/v1/patients/cards/manipulation-records/{id}',
            delete: '/api/v1/patients/cards/manipulation-records/{id}',
        };
    }
}

export default ManipulationRecord;
