import moment from 'moment';
import BaseModel from '@/models/base-model';
import Patient from '@/models/patient';
import Employee from '@/models/employee';
import WaitListRecord from '@/models/wait-list-record';
import {
    required,
    attributeEquals,
    assertion
} from '@/services/validation';
import CONSTANT from '@/constants';

/**
 * Call model
 */
class Call extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Call';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            is_first: 1,
            date: null,
            time: null,
            comment: null,
            call_result_id: null,
            clinic_id: null,
            employee_id: null,
            specialization_id: null,
            call_request_id: null,
            contact_id: null,
            contact_type: CONSTANT.USER.TYPE.PATIENT,
            contact: {},
            call_delete_reason_id: null,
            delete_reason_comment: '',
            delete_reason: false,
            comment_required: false,
            wait_list_record: {},
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            contact: (value) => this.convertContact(value),
            wait_list_record: (value) => this.initSubModel(WaitListRecord, value),
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            date: required,
            time: required,
            is_first: required,
            call_result_id: required,
            employee_id: required,
            specialization_id: required.or(assertion(() => {
                return !this.is_patient;
            })),
            clinic_id: required.or(assertion(() => {
                return !this.is_patient;
            })),
            call_delete_reason_id: required.or(attributeEquals('delete_reason', false)),
            delete_reason_comment: required.or(attributeEquals('comment_required', false)),
            contact: (value) => this.validateSubmodel(value, null, this.getSubmodelAttributes()),
            wait_list_record: (value) => this.validateSubmodel(value,() => _.get(this, 'waitListRequired', false,['period_from'])),
        };
    }

    /**
     * Get set of submodel attributes
     *
     * @returns {array}
     */
    getSubmodelAttributes() {
        if (this.contact instanceof Patient) {
            return [
                'firstname',
                'lastname',
                'middlename',
                'gender',
                'status',
                'location',
                'birthday',
                'comment',
                'mailing_analysis',
                'source_id',
                'contact_details',
                'clinics',
            ];
        }
        if (this.contact instanceof Employee) {
            return [
                'first_name',
                'middle_name',
                'last_name',
                'phone',
            ];
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/calls',
            fetch: '/api/v1/calls/{id}',
            update: '/api/v1/calls/{id}',
        }
    }

    /**
     * Get date time
     *
     * @returns {Moment}
     */
    get date_time() {
        return moment(`${this.date} ${this.time}`);
    }

    /**
     * Get is patient
     *
     * @returns {bool}
     */
    get is_patient() {
        return (this.contact instanceof Patient)
            && this.contact.is_patient;
    }

    /**
     * Get source if patient
     *
     * @returns {number}
     */
    get source() {
        return (this.contact instanceof Patient) ? this.contact.source_name : null;
    }

    /**
     * Get contact full name
     *
     * @returns {string}
     */
    get contact_name() {
        return this.contact.full_name;
    }

    /**
     * Get contact phone
     *
     * @returns {string}
     */
    get phone_number() {
        return (this.contact instanceof Patient)
            ? [this.contact.primary_phone_number, this.contact.secondary_phone_number].filter(v => _.isFilled(v))
            : [this.contact.phone].filter(v => _.isFilled(v));
    }

    /**
     * Check if contact is related to patient model
     *
     * @returns {bool}
     */
    get is_patient_contact() {
        return (this.contact instanceof Patient);
    }

    /**
     * Check if contact is related to employee model
     *
     * @returns {bool}
     */
    get is_employee_contact() {
        return (this.contact instanceof Employee);
    }

    /**
     * Convert contact instance
     *
     * @param {*} contact
     *
     * @returns {*}
     */
    convertContact(object) {
        if (object instanceof Patient) {
            return object;
        }
        if (object instanceof Employee) {
            return object;
        }
        if (object !== null) {
            if (object[CONSTANT.USER.TYPE.PATIENT] !== undefined) {
                return this.castToInstance(Patient, object[CONSTANT.USER.TYPE.PATIENT]);
            }
            if (object[CONSTANT.USER.TYPE.EMPLOYEE] !== undefined) {
                return this.castToInstance(Employee, object[CONSTANT.USER.TYPE.EMPLOYEE]);
            }
        }
        return new Patient();
    }

    /**
     * Verify model has wait list record
     *
     * @param {*} waitListRecord
     *
     * @returns {bool}
     */
    waitListRecordIsMissing() {
        let waitListReasonRequired = _.get(this, 'waitListReasonRequired', false);
        let waitListRequired = _.get(this, 'waitListRequired', false);
        return !waitListReasonRequired && !waitListRequired;
    }

    /**
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        attributes.contact_id = attributes.contact.id;
        if (this.is_patient_contact) {
            attributes.contact_type = CONSTANT.USER.TYPE.PATIENT;
        } else if (this.is_employee_contact) {
            attributes.contact_type = CONSTANT.USER.TYPE.EMPLOYEE;
        }
        attributes.contact = _.pick(attributes.contact, [
            'id',
            ...this.getSubmodelAttributes(),
        ]);

        if (this.waitListRecordIsMissing()) {
            delete attributes.wait_list_record;
        }
        return attributes;
    }
}

export default Call;
