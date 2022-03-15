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
            confidant_person_id: null,
            type: null,
            number: null,
            issued_by: null,
            issued_at: null,
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
            issued_at: required.and(date).and(before(Date.now())),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/ehealth/patient/relation-documents',
            fetch: '/api/v1/ehealth/patient/relation-documents/{id}',
            update: '/api/v1/ehealth/patient/relation-documents/{id}',
            delete: '/api/v1/ehealth/patient/relation-documents/{id}',
        }
    }
}

export default PatientDocument;
