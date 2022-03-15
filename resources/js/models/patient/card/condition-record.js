import BaseRecord from './base-record';
import {
    required,
    maxlen
} from '@/services/validation';

class ConditionRecord extends BaseRecord
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            card_specialization_id: null,
            appointment_id: null,
            at: null,
            at2: null,
            frequency: null,
            temperature: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            at: required.and(maxlen(30)),
            at2: required.and(maxlen(30)),
            frequency: required.and(maxlen(30)),
            temperature: required.and(maxlen(30)),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/condition-records',
            fetch: '/api/v1/patients/cards/condition-records/{id}',
            update: '/api/v1/patients/cards/condition-records/{id}',
            delete: '/api/v1/patients/cards/condition-records/{id}',
        };
    }
}

export default ConditionRecord;
