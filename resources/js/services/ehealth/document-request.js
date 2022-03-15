import BaseRequest from './base-request';
import {
    required,
    ukrSpelling,
} from '@/services/validation';

const DOC_TYPES = {
    'birth_certificate': 'BIRTH_CERTIFICATE',
    'birth_certificate_foreign': 'BIRTH_CERTIFICATE_FOREIGN',
    'cp_certificate': 'COMPLEMENTARY_PROTECTION_CERTIFICATE',
    'national_id': 'NATIONAL_ID',
    'passport': 'PASSPORT',
    'permanent_residence_permit': 'PERMANENT_RESIDENCE_PERMIT',
    'refugee_certificate': 'REFUGEE_CERTIFICATE',
    'temporary_certificate': 'TEMPORARY_CERTIFICATE',
    'temporary_passport': 'TEMPORARY_PASSPORT',
};

class DocumentRequest extends BaseRequest
{
    constructor(doc) {
        super(doc);
        this.addProp('type', () => this.fromDict(doc.type, DOC_TYPES), [required]);
        this.addProp('number', () => doc.number, [required, ukrSpelling]);
        this.addProp('issued_by', () => doc.issued_by, [ukrSpelling]);
        this.addProp('issued_at', () => doc.issued_at);
    }
}

export default DocumentRequest;