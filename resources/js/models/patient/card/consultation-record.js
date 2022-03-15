import BaseRecord from './base-record';
import {
    required,
} from '@/services/validation';

class ConsultationRecord extends BaseRecord
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            card_specialization_id: null,
            appointment_id: null,
            consultations: [],
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            card_specialization_id: required,
            appointment_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/consultation-records',
            fetch: '/api/v1/patients/cards/consultation-records/{id}',
            update: '/api/v1/patients/cards/consultation-records/{id}',
        };
    }
}

export default ConsultationRecord;
