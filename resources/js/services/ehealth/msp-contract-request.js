import BaseRequest from './base-request';
import SubcontractorRequest from './msp-subcontractor-request';
import { 
    required,
    requiredArray,
    ukrSpelling,
    ehealthContractNumber,
    mfo,
} from '@/services/validation';
import ClinicRepository from '@/repositories/clinic';
import FileAttachmentRepository from '@/repositories/file-attachment';
import moment from 'moment';
import Msp from '@/models/msp';
import CONSTANT from '@/constants';

const FORMS = {
    'pmd1': 'PMD_1',
};

class PaymentDetailsRequest extends BaseRequest
{
    constructor(contract) {
        super(contract);
        this.addProp('bank_name', () => contract.payer_bank, [required, ukrSpelling], 'payer_bank');
        this.addProp('MFO', () => contract.payer_mfo, [required.and(mfo)], 'payer_mfo');
        this.addProp('payer_account', () => contract.payer_account_number, [required], 'payer_account');
    }
}

class MspContractRequest extends BaseRequest
{
    constructor(contract, msp, consentText) {
        super(contract);
        this._msp = msp;
        this._fileStats = null;
        this._clinicIdMap = null;
        this.addProp('contractor_owner_id', () => msp.owner.ehealth_id);
        this.addProp('contractor_base', () => contract.contractor_base, [required, ukrSpelling]);
        this.addProp('contractor_payment_details', new PaymentDetailsRequest(contract), false, '');
        this.addProp('previous_request_id', () => contract.ehealth_request_id);
        this.addProp('contractor_divisions', () => this.makeDivisions(contract), [requiredArray]);
        this.addProp('external_contractor_flag', () => contract.subcontractors.length !== 0);
        this.addProp('id_form', () => this.fromDict(contract.form_type, FORMS), [required], 'form_type');
        this.addProp('statute_md5', () => this.makeMd5Sum(contract.statute_id));
        this.addProp('additional_document_md5', () => this.makeMd5Sum(contract.additional_document_id));
        this.addProp('consent_text', () => consentText);
        this.addProp('external_contractors', () => this.makeSubcontractors(contract.subcontractors));

        if (_.isVoid(contract.contract_number)) {
            this.addProp('start_date', () => contract.start_date, [
                required.and((val) => this.validateStartDate(val)),
                () => this.validateActiveContract(),
            ]);
            this.addProp('end_date', () => contract.end_date, [
                required.and((val) => this.validateEndDate(val)),
            ]);
        } else {
            this.addProp('contract_number', () => contract.contract_number, [ehealthContractNumber]);
        }
    }

    makeDivisions(contract) {
        return this.getClinicIdMap().then((map) => {
            return contract.clinics.map((clinic) => map[clinic]);
        });
    }

    makeMd5Sum(id) {
        if (this._fileStats === null) {
            let id = [
                this._subject.statute_id,
                this._subject.additional_document_id,
            ].filter((v) => _.isFilled(v));
            if (id.length === 0) {
                return Promise.resolve(null);
            }
            let repository = new FileAttachmentRepository();
            this._fileStats = repository.fetch({id});
        }
        return this._fileStats.then((result) => {
            let file = _.findById(result.rows, id);
            if (file !== undefined) {
                return file.md5_sum;
            }
            return null;
        });
    }

    makeSubcontractors(subcontractors) {
        if (subcontractors.length === 0) {
            return null;
        }
        return this.getClinicIdMap().then((map) => {
            return Promise.all(subcontractors.map((subcontractor, index) => {
                let request = new SubcontractorRequest(subcontractor, map, this._subject);
                return request.transform().then(() => {
                    request.getErrors().forEach((err) => {
                        this.addError(`subcontractors.${index}.${err.prop}`, err.error);
                    });
                    return request.getData();
                });
            }));
        });
    }

    getClinicIdMap() {
        if (this._clinicIdMap === null) {
            let repository = new ClinicRepository();
            this._clinicIdMap = repository.fetch({
                msp: this._msp.id,
                active_in_ehealth: true,
            }, null, ['none'], 1, 500).then((result) => {
                let map = {};
                result.rows.forEach((clinic) => {
                    map[clinic.id] = clinic.ehealth_id;
                })
                return map;
            })
        }
        return this._clinicIdMap;
    }

    validateStartDate(val) {
        let min = moment().startOf('year');
        let max = moment().add(1, 'years').endOf('year');
        if (min.isAfter(val) || max.isBefore(val)) {
            return __('Дата начала должна быть в этом или следующем году');
        }
    }

    validateEndDate(val) {
        if (_.isFilled(this._subject.start_date)) {
            let start_date = this._subject.start_date;
            if (!moment(val).isAfter(start_date)) {
                return __('Дата окончания должна быть позже даты начала');
            }
            if (moment(val).subtract(1, 'years').isAfter(start_date)) {
                return __('Длительность действия договора не может превышать один год');
            }
        }
    }

    validateActiveContract() {
        if (_.isFilled(this._subject.start_date) && _.isFilled(this._subject.end_date)) {
            let model = new Msp({id: this._msp.id});
            return model.fetch(['contracts']).then(() => {
                let start = moment(this._subject.start_date);
                let end = moment(this._subject.end_date);
                let hasActive = model.contracts.some((contract) => {
                    if (contract.id === this._subject.id) {
                        return false;
                    }
                    if (contract.ehealth_status !== CONSTANT.EHEALTH.CONTRACT_STATUS.VERIFIED) {
                        return false;
                    }
                    if (start.isBetween(contract.start_date, contract.end_date, undefined, '[]')) {
                        return true;
                    }
                    if (end.isBetween(contract.start_date, contract.end_date, undefined, '[]')) {
                        return true;
                    }
                    return false;
                });
                if (hasActive) {
                    return __('У вас уже есть активный договор на данный период');
                }
            });
        }
    }
}

export default MspContractRequest;