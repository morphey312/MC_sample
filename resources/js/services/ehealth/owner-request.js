import BaseEmployeeRequest from './base-employee-request';
import {
    required,
    taxId,
    email,
} from '@/services/validation';

class OwnerRequest extends BaseEmployeeRequest
{
    constructor(owner, position) {
        super(owner);
        this.addProp('employee_id', () => owner.ehealth_id);
        this.addProp('tax_id', () => owner.tax_id, [required, taxId]);
        this.addProp('email', () => owner.email, [required, email]);
        this.addProp('position', () => position, [required]);
    }
}

export default OwnerRequest;