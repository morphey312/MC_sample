import BaseRequest from './base-request';
import { 
    required,
    ukrSpelling,
} from '@/services/validation';

const LEVELS = {
    'basic': 'BASIC',
    'first': 'FIRST',
    'highest': 'HIGHEST',
    'not_applicable': 'NOT_APPLICABLE',
    'second': 'SECOND',
};

const TYPES = {
    'awarding': 'AWARDING',
    'confirmation': 'DEFENSE',
};

class SpecialityRequest extends BaseRequest
{
    constructor(speciality) {
        super(speciality);
        this.addProp('speciality', () => speciality.speciality_code, [required]);
        this.addProp('speciality_officio', () => speciality.primary);
        this.addProp('level', () => this.fromDict(speciality.level, LEVELS), [required]);
        this.addProp('qualification_type', () => this.fromDict(speciality.qualification_type, TYPES), [required]);
        this.addProp('attestation_name', () => speciality.attestation_name, [required, ukrSpelling]);
        this.addProp('attestation_date', () => speciality.attestation_date, [required]);
        this.addProp('valid_to_date', () => speciality.valid_to_date);
        this.addProp('certificate_number', () => speciality.certificate_number, [required]);
    }
}

export default SpecialityRequest;