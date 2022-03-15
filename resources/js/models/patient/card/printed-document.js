import BaseRecord from './base-record';

class PrintedDocument extends BaseRecord
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
            document_name: null,
            html: null,
            header: null,
            footer: null,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/printed-documents',
        };
    }
}

export default PrintedDocument;
