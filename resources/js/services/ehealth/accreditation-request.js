import BaseRequest from './base-request';
import { 
    required,
    ukrSpelling,
} from '@/services/validation';

const CATEGORIES = {
    'first': 'FIRST',
    'second': 'SECOND',
    'highest': 'HIGHEST',
    'no_accreditation': 'NO_ACCREDITATION',
};

class AccreditationRequest extends BaseRequest
{
    constructor(object) {
        super(object);
        let nope = object.accreditation_category === 'no_accreditation';
        this.addProp('category', () => this.fromDict(object.accreditation_category, CATEGORIES), [required], 'accreditation_category');
        this.addProp('issued_date', () => nope ? null : object.accreditation_issued_date, false, 'accreditation_issued_date');
        this.addProp('expiry_date', () => nope ? null : object.accreditation_expiry_date, false, 'accreditation_expiry_date');
        this.addProp('order_no', () => nope ? '' : object.accreditation_order_no, nope ? false : [required, ukrSpelling], 'accreditation_order_no');
        this.addProp('order_date', () => nope ? null : object.accreditation_order_date, nope ? false : [required], 'accreditation_order_date');
    }
}

export default AccreditationRequest;