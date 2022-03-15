import BaseRequest from './base-request';
import { 
    required,
    ukrSpelling,
} from '@/services/validation';
import moment from 'moment';

const SERVICES = {
    'phc_services': 'PHC_SERVICES',
};

class SubcontractorContractRequest extends BaseRequest
{
    constructor(contractor, contract) {
        super(contractor);
        this.addProp('number', () => contractor.contract_number, [required, ukrSpelling]);
        this.addProp('issued_at', () => contractor.issued_at, [required]);
        this.addProp('expires_at', () => contractor.expires_at, [required.and((val) => this.validateExpDate(val, contract))]);
    }

    validateExpDate(date, contract) {
        let start = moment(contract.start_date);
        if (!start.isBefore(date)) {
            return __('Дата истечения срока договора субподрядчика должна быть позже даты начала действия данного договора');
        }
    }
}

class SubcontractorRequest extends BaseRequest
{
    constructor(contractor, clinicMap, contract) {
        super(contractor);
        this._clinicMap = clinicMap;
        this.addProp('legal_entity_id', () => contractor.ehealth_id);
        this.addProp('contract', new SubcontractorContractRequest(contractor, contract));
        this.addProp('divisions', () => this.makeDivisions(contractor.clinics));
    }

    makeDivisions(clinics) {
        return clinics.map((clinic) => ({
            id: this._clinicMap[clinic.id],
            medical_service: this.fromDict(clinic.service, SERVICES),
        }));
    }
}

export default SubcontractorRequest;