import BaseModel from '@/models/base-model';

class FileAttachment extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            url: null,
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            fetch: '/api/v1/file-attachments/{id}',
            delete: '/api/v1/file-attachments/{id}',
        }
    }
}

export default FileAttachment;