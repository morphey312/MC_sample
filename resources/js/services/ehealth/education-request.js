import BaseRequest from './base-request';
import { 
    required,
    ukrSpelling,
} from '@/services/validation';

const DEGREES = {
    'bachelor': 'BACHELOR',
    'expert': 'EXPERT',
    'junior_expert': 'JUNIOR_EXPERT',
    'master': 'MASTER',
};

class EducationRequest extends BaseRequest
{
    constructor(education) {
        super(education);
        this.addProp('country', () => education.country_code, [required]);
        this.addProp('city', () => education.city, [required, ukrSpelling]);
        this.addProp('institution_name', () => education.institution_name, [required, ukrSpelling]);
        this.addProp('issued_date', () => education.issued_date);
        this.addProp('diploma_number', () => education.diploma_number, [required]);
        this.addProp('degree', () => this.fromDict(education.degree, DEGREES), [required]);
        this.addProp('speciality', () => education.speciality, [required, ukrSpelling]);
    }
}

export default EducationRequest;