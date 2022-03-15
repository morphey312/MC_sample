import BaseModel from '@/models/base-model';
import DocumentSignature from './document-signature';
import {
    required,
    assertion,
} from '@/services/validation';
import CONSTANTS from '@/constants';

/**
 * TreatmentCourseDocument model
 */
class Document extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            type: null,
            treatment_course_id: null,
            signer_id: null,
            date_signed: null,
            attachments: [],
            attachments_data: [],
            signatures: [],
        };
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            treatment_course_id: required,
            type: assertion(() => {
                return Object.values(CONSTANTS.STATIONAR_MOZ_BLANK).indexOf(this.type) !== -1;
            }),
        };
    }

    /** 
     * @inheritdoc
     */
    mutations() {
        return {
            signatures: (value) => _.isArray(value) ? value.map((sig) => this.initSubModel(DocumentSignature, sig)) : [],
        }
    }

     /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/treatment-courses/documents',
            update: '/api/v1/treatment-courses/documents/{id}',
        }
    }

    /** 
     * Last signature date
     * 
     * @return {string}
     */
    get last_sign_date() {
        return this.signatures.length === 0 ? null : this.signatures[0].date;
    }

    /** 
     * Last signature employee
     * 
     * @return {string}
     */
    get last_signer_name() {
        return this.signatures.length === 0 ? null : this.signatures[0].employee_name;
    }

    /** 
     * Check document has signatures
     * 
     * @return {bool}
     */
    get signed() {
        return this.signatures.length !== 0;
    }
}

export default Document;