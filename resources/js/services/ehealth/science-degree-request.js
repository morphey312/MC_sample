import BaseRequest from './base-request';
import { 
    required,
    ukrSpelling,
} from '@/services/validation';

const DEGREES = {
    'candidate_of_science': 'CANDIDATE_OF_SCIENCE',
    'doctor_of_science': 'DOCTOR_OF_SCIENCE',
    'phd': 'PHD',
};

class ScienceDegreeRequest extends BaseRequest
{
    constructor(degree) {
        super(degree);
        this.addProp('country', () => degree.country_code, [required]);
        this.addProp('city', () => degree.city, [required, ukrSpelling]);
        this.addProp('degree', () => this.fromDict(degree.degree, DEGREES), [required]);
        this.addProp('institution_name', () => degree.institution_name, [required, ukrSpelling]);
        this.addProp('diploma_number', () => degree.diploma_number, [required]);
        this.addProp('speciality', () => degree.speciality, [required, ukrSpelling]);
        this.addProp('issued_date', () => degree.issued_date);
    }
}

export default ScienceDegreeRequest;