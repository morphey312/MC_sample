import BaseRecord from './base-record';

class ArchivedCard extends BaseRecord {
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            number: null,
            specialization_id: null,
            attachments: []
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            update: '/api/v1/patients/cards/archived/{id}',
        };
    }
}

export default ArchivedCard;
