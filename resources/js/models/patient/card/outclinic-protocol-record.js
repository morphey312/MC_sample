import BaseRecord from './base-record';

class OutclinicProtocolRecord extends BaseRecord {
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            card_specialization_id: null,
            appointment_id: null,
            attachments: [],
            allowed_in_ok: true
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/outclinic-protocol-records',
            fetch: '/api/v1/patients/cards/outclinic-protocol-records/{id}',
            update: '/api/v1/patients/cards/outclinic-protocol-records/{id}',
            delete: '/api/v1/patients/cards/outclinic-protocol-records/{id}',
        };
    }
}

export default OutclinicProtocolRecord;
