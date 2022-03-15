import BaseRepository from '@/repositories/base-repository';
import UploadedDocument from '@/models/patient/uploaded-document';

class UploadedDocumentRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/uploads';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new UploadedDocument(row);
    }
}

export default UploadedDocumentRepository;