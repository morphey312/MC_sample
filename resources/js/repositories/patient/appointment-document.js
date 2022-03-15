import BaseRepository from '@/repositories/base-repository';
import AppointmentDocument from "../../models/patient/appointment-document";

class AppointmentDocumentRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/appointment-document';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new AppointmentDocument(row);
    }
}

export default AppointmentDocumentRepository;
