import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';

/**
 * TreatmentCourseDocument model
 */
class DocumentSignature extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            file_id: null,
            document_id: null,
        };
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            file_id: required,
            document_id: required,
        };
    }

     /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/treatment-courses/documents/sign',
        }
    }
}

export default DocumentSignature;