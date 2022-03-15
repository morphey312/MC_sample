import BaseRepository from '@/repositories/base-repository';
import FileAttachment from '@/models/file-attachment';

class FileAttachmentRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/file-attachments';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new FileAttachment(row);
    }
}

export default FileAttachmentRepository;