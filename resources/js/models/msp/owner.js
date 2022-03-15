import Employee  from '@/models/employee';
import {
    required,
    maxlen,
    phoneNumber,
    taxId,
    missing,
    email,
    STRING_MAX_LEN,
} from '@/services/validation';

class MspOwner extends Employee
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            ehealth_id: null,
            phone: null,
            additional_phone: null,
            email: null,
            first_name: null,
            last_name: null,
            middle_name: null,
            birth_date: null,
            gender: null,
            tax_id: null,
            employee_documents: [],
            user: {},
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            phone: required.and(phoneNumber),
            additional_phone: phoneNumber.or(missing),
            email: required.and(email),
            first_name: required.and(maxlen(STRING_MAX_LEN)),
            last_name: required.and(maxlen(STRING_MAX_LEN)),
            middle_name: maxlen(STRING_MAX_LEN),
            tax_id: required.and(taxId),
            birth_date: required,
            gender: required,
            user: (value) => this.validateSubmodel(value, () => this.isNew(), [
                'login',
                'password',
            ]),
        };
    }

    /** 
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        if (!this.isNew()) {
            delete attributes.user;
        }
        return attributes;
    }
}

export default MspOwner;