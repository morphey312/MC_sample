import BaseRecord from './base-record';

class ProtocolRecord extends BaseRecord
{
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            card_specialization_id: null,
            appointment_id: null,
            file_id: null,
            template_id: null,
            data: null,
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/protocol-records',
            fetch: '/api/v1/patients/cards/protocol-records/{id}',
            update: '/api/v1/patients/cards/protocol-records/{id}',
            delete: '/api/v1/patients/cards/protocol-records/{id}',
        };
    }
}

export default ProtocolRecord;