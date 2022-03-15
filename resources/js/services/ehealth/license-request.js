import BaseRequest from './base-request';
import { 
    required,
    ukrSpelling,
} from '@/services/validation';

const TYPES = {
    'msp': 'MSP',
    'pharmacy': 'PHARMACY',
};

class LicenseRequest extends BaseRequest
{
    constructor(object) {
        super(object);
        let licenseChanged = false;
        let changes = object.changed();
        if (changes) {
            let watch = [
                'license_type', 'license_number', 'license_issued_by', 
                'license_issued_date', 'license_expiry_date', 'license_active_from_date',
                'license_subject', 'license_order_no'
            ];
            if (_.intersection(changes, watch).length !== 0) {
                licenseChanged = true;
            }
        }
        if (object.license_ehealth_id && !licenseChanged) {
            this.addProp('id', () => object.license_ehealth_id);
        } else {
            this.addProp('type', () => this.fromDict(object.license_type, TYPES), [required], 'license_type');
            this.addProp('license_number', () => object.license_number, [ukrSpelling], 'license_number');
            this.addProp('issued_by', () => object.license_issued_by, [required, ukrSpelling], 'license_issued_by');
            this.addProp('issued_date', () => object.license_issued_date, [required], 'license_issued_date');
            this.addProp('expiry_date', () => object.license_expiry_date, false, 'license_expiry_date');
            this.addProp('active_from_date', () => object.license_active_from_date, [required], 'license_active_from_date');
            this.addProp('what_licensed', () => object.license_subject, [ukrSpelling], 'license_subject');
            this.addProp('order_no', () => object.license_order_no, [required, ukrSpelling], 'license_order_no');
        }
    }
}

export default LicenseRequest;