import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    TEXT_MAX_LEN
} from '@/services/validation';

class UploadedDocument extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            patient_id: null,
            file_id: null,
            type: null,
            description: null,
            is_protected: true,
        };
    }
    
    /** 
     * @inheritdoc
     */
    validation() {
        return {
            file_id: required,
            type: required,
            description: maxlen(TEXT_MAX_LEN),
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/uploads',
            fetch: '/api/v1/patients/uploads/{id}',
            update: '/api/v1/patients/uploads/{id}',
            delete: '/api/v1/patients/uploads/{id}',
        }
    }
}

export default UploadedDocument;