import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
    missing,
    assertion,
    after,
    before,
    date
} from '@/services/validation';
import CONSTANT from "@/constants";

class PatientDocument extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            owner_id: null,
            owner_type: null,
            type: null,
            number: null,
            issued_by: null,
            issued_at: null,
            expiration_date: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            type: required,
            number: required.and(maxlen(STRING_MAX_LEN)),
            issued_by: required.and(maxlen(TEXT_MAX_LEN)),
            expiration_date: after(Date.now()).or(missing.and(assertion(() => {
                return this.type !== CONSTANT.EHEALTH_PATIENT.DOCUMENTS_TYPE.NATIONAL_ID
            }))),
            issued_at: required.and(date).and(before(Date.now())),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/ehealth/patient/documents',
            fetch: '/api/v1/ehealth/patient/documents/{id}',
            update: '/api/v1/ehealth/patient/documents/{id}',
            delete: '/api/v1/ehealth/patient/documents/{id}',
        }
    }
}

export default PatientDocument;
