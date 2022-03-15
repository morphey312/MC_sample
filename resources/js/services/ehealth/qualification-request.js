import BaseRequest from './base-request';
import { 
    required,
    ukrSpelling,
} from '@/services/validation';

const TYPES = {
    'clinical_residency': 'CLINICAL_RESIDENCY',
    'information_courses': 'INFORMATION_COURSES',
    'internship': 'INTERNSHIP',
    'postgraduate': 'POSTGRADUATE',
    'reattestation': 'REATTESTATION',
    'specialization': 'SPECIALIZATION',
    'traineeship': 'STAZHUVANNYA',
    'topic_improvement': 'TOPIC_IMPROVEMENT',
};

class QualificationRequest extends BaseRequest
{
    constructor(qualification) {
        super(qualification);
        this.addProp('type', () => this.fromDict(qualification.type, TYPES), [required]);
        this.addProp('institution_name', () => qualification.institution_name, [required, ukrSpelling]);
        this.addProp('speciality', () => qualification.speciality, [required, ukrSpelling]);
        this.addProp('issued_date', () => qualification.issued_date);
        this.addProp('certificate_number', () => qualification.certificate_number, [required]);
        this.addProp('valid_to', () => qualification.valid_to);
        this.addProp('additional_info', () => qualification.additional_info, [ukrSpelling]);
    }
}

export default QualificationRequest;