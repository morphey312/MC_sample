import BaseRepository from '@/repositories/base-repository';
import PatientDocument from "@/models/patient-document";

class PatientDocumentRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/patient-documents';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new PatientDocument(row);
    }
}

export default PatientDocumentRepository;
