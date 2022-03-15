import BaseModel from '@/models/base-model';
import {
    required,
    email,
    missing,
    maxlen,
    phoneNumber,
    attributeMissing,
    STRING_MAX_LEN
} from '@/services/validation';

class ContactDetails extends BaseModel 
{
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            email: null,
            primary_phone_number: null,
            primary_phone_clinic: null,
            secondary_phone_number: null,
            secondary_phone_clinic: null,
            primary_phone_comment: null,
            secondary_phone_comment: null,
        };
    }
    
    /** 
     * @inheritdoc
     */
    validation() {
        return {
            email: email.and(maxlen(STRING_MAX_LEN)).or(missing),
            primary_phone_number: required.and(phoneNumber.and(maxlen(STRING_MAX_LEN))),
            secondary_phone_number: phoneNumber.and(maxlen(STRING_MAX_LEN)).or(missing),
            primary_phone_clinic: attributeMissing('primary_phone_number').or(required),
            secondary_phone_clinic: attributeMissing('secondary_phone_number').or(required),
        };
    }
}

export default ContactDetails;