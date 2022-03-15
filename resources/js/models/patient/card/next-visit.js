import BaseRecord from './base-record';

class NextVisit extends BaseRecord
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            card_specialization_id: null,
            next_visit_date: null,
            call_request_id: null,
            appointment_id: null
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/next-visit',
            update: '/api/v1/patients/cards/next-visit/{id}',
            delete: '/api/v1/patients/cards/next-visit/{id}'
        };
    }
}

export default NextVisit;
