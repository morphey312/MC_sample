import ProcessLog from '@/models/calls/process-log';
import CONSTANT from '@/constants';

const UNKNOWN = 'unknown';

class Contact
{
    /**
     * Constructor
     * 
     * @param {string|number} id
     * @param {string} type
     * @param {string} name
     * @param {array} numbers
     * @param {string} email
     */ 
    constructor(id, type, name, numbers, email = null) {
        this._id = id;
        this._type = type;
        this._name = name;
        this._numbers = numbers;
        this._email = email;
    }
    
    /**
     * Identify contact
     * 
     * @param {string|number} id
     * @param {string} type
     * @param {string} name
     */ 
    identifyAs(id, type, name) {
        this._id = id;
        this._type = type;
        this._name = name;
    }

    /**
     * Check if contact is related to number
     * 
     * @param {string} number 
     */
    hasNumber(number) {
        return this._numbers.some((item) => item.number === number);
    }
    
    /**
     * Get contact UID
     * 
     * @returns {string}
     */ 
    get uid() {
        if (this.isUnknown) {
            return `${UNKNOWN}:${this.defaultNumber}`;
        }
        return `${this._type}:${this._id}`;
    }
    
    /**
     * Check contact is unknown
     * 
     * @returns {bool}
     */ 
    get isUnknown() {
        return this._id === null;
    }
    
    /**
     * Check if contact related to a patient 
     * 
     * @returns {bool}
     */ 
    get isPatientContact() {
        return this._type === CONSTANT.USER.TYPE.PATIENT 
            && this._id !== null;
    }
    
    /**
     * Check if contact related to an employee 
     * 
     * @returns {bool}
     */ 
    get isEmployeeContact() {
        return this._type === CONSTANT.USER.TYPE.EMPLOYEE 
            && this._id !== null;
    }
    
    /**
     * Get contact id
     * 
     * @returns {string|number}
     */ 
    get id() {
        return this._id;
    }
    
    /**
     * Get contact type
     * 
     * @returns {string}
     */ 
    get type() {
        return this._type;
    }
    
    /**
     * Get contact name
     * 
     * @returns {string}
     */ 
    get name() {
        return this._name;
    }
    
    /**
     * Get contact numbers
     * 
     * @returns {array}
     */ 
    get numbers() {
        return this._numbers;
    }
    
    /**
     * Get email
     * 
     * @returns {string}
     */ 
    get email() {
        return this._email;
    }

    /**
     * Set email
     * 
     * @returns {string}
     */ 
    set email(email) {
        this._email = email;
    }
    
    /**
     * Get default number
     * 
     * @returns {object}
     */ 
    get defaultNumber() {
        return this._numbers.length === 0 ? undefined : this._numbers[0].number;
    }
    
    /**
     * Serialize contact
     * 
     * @returns {object}
     */ 
    serialize() {
        return {
            id: this._id,
            type: this._type,
            name: this._name,
            numbers: this._numbers,
            email: this._email,
        };
    }
    
    /**
     * Unserialize contact
     * 
     * @param {object} data
     */ 
    static unserialize(data) {
        return new Contact(data.id, data.type, data.name, data.numbers, data.email);
    }
}

class PatientContact extends Contact
{
    /**
     * Constructor
     * 
     * @param {Patient} patient
     */ 
    constructor(patient) {
        super(
            patient.id, 
            CONSTANT.USER.TYPE.PATIENT, patient.full_name, 
            [{
                number: patient.contact_details.primary_phone_number,
                comment: patient.contact_details.primary_phone_comment,
                clinic: patient.contact_details.primary_phone_clinic,
            }, {
                number: patient.contact_details.secondary_phone_number,
                comment: patient.contact_details.secondary_phone_comment,
                clinic: patient.contact_details.secondary_phone_clinic,
            }].filter((item) => !_.isNil(item.number) && item.number !== ''),
            patient.contact_details.email
        );
        this._patient = patient;
    }
    
    /**
     * Get related patient
     * 
     * @returns {Patient}
     */ 
    get patient() {
        return this._patient;
    }
}

class EmployeeContact extends Contact
{
    /**
     * Constructor
     * 
     * @param {Employee} employee
     */ 
    constructor(employee) {
        super(employee.id, CONSTANT.USER.TYPE.EMPLOYEE, employee.full_name, 
            [{
                number: employee.phone,
                comment: __('Мобильный'),
            }]
            .concat(employee.employee_clinics.map((clinic) => {
                return {
                    number: clinic.sip_number,
                    comment: clinic.clinic_name,
                };
            }))
            .filter((item) => _.isFilled(item.number))
        );
        this._employee = employee;
    }
    
    /**
     * Get related employee
     * 
     * @returns {Employee}
     */ 
    get employee() {
        return this._employee;
    }
}

class ProcessState
{
    /**
     * Constructor
     */ 
    constructor() {
        this._currentContact = undefined;
        this._contactPool = [];
        this._processLog = new ProcessLog();
        this._phoneNumber = undefined;
        this._processing = false;
    }
    
    /**
     * Get current contact
     * 
     * @returns {Contact}
     */ 
    get currentContact() {
        return this._currentContact;
    }
    
    /**
     * Get process log record
     * 
     * @returns {ProcessLog}
     */ 
    get processLog() {
        return this._processLog;
    }
    
    /**
     * Set process log record
     * 
     * @param {ProcessLog} log
     * 
     * @returns {ProcessLog}
     */ 
    set processLog(log) {
        return this._processLog = log;
    }
    
    /**
     * Get contact pool
     * 
     * @returns {array}
     */ 
    get contactPool() {
        return this._contactPool;
    }
    
    /**
     * Get phone number
     * 
     * @returns {string} 
     */ 
    get phoneNumber() {
        return this._phoneNumber;
    }
    
    /**
     * Set phone number
     * 
     * @param {string} number
     * 
     * @returns {string} 
     */ 
    set phoneNumber(number) {
        return this._phoneNumber = number;
    }
    
    /**
     * Check if processing is active
     * 
     * @returns {bool}
     */ 
    get processing() {
        return this._processing;
    }
    
    /**
     * Get process log clinic
     * 
     * @returns {number}
     */ 
    get clinic() {
        return this._processLog.clinic;
    }
    
    /**
     * Add contact to the pool
     * 
     * @param {Contact} contact
     */ 
    addContact(contact) {
        if (this.findContact(contact.uid) === undefined) {
            this._contactPool.push(contact);
        }
    }
    
    /**
     * Find contact by uid
     * 
     * @param {string} uid
     * 
     * @returns {Contact}
     */ 
    findContact(uid) {
        return _.find(this._contactPool, (contact) => contact.uid === uid);
    }

    /**
     * Find contact by number
     * 
     * @param {string} number 
     */
    findContactByNumber(number) {
        return _.find(this._contactPool, (contact) => contact.hasNumber(number));
    }
    
    /**
     * Change current contact
     * 
     * @param {string} uid
     * 
     * @returns {Contact}
     */ 
    selectContact(uid) {
        if (this._currentContact === undefined || this._currentContact.uid !== uid) {
            this._currentContact = this.findContact(uid);
            if (this._currentContact !== undefined) {
                this._phoneNumber = this._currentContact.defaultNumber;
            }
        }
        return this._currentContact;
    }
    
    /**
     * Remove contact
     * 
     * @param {string} uid
     */ 
    removeContact(uid) {
        this._contactPool = this._contactPool.filter((item) => item.uid !== uid);
        if (this._currentContact !== undefined && this._currentContact.uid === uid) {
            this._currentContact = undefined;
        }
    }
    
    /**
     * Replace unknown contacts with the known one
     * 
     * @param {Contact} newContact
     */ 
    replaceContact(newContact) {
        this._contactPool = this._contactPool.map((contact) => {
            if (contact.isUnknown) {
                let common = _.intersectionWith(contact.numbers, newContact.numbers, (a, b) => a.number === b.number);
                if (common.length !== 0) {
                    return newContact;
                }
            }
            return contact;
        });
        
        // in case nothing was replaced
        this.addContact(newContact);
        
        if (this._currentContact !== undefined && this._currentContact.isUnknown) {
            this._currentContact = newContact;
        }
    }
    
    /**
     * Update contact data
     * 
     * @param {Contact} newContact
     */ 
    updateContact(newContact) {
        this._contactPool = this._contactPool.map((contact) => {
            if (contact.uid === newContact.uid) {
                return newContact;
            }
            return contact;
        });
    }
    
    /**
     * Select contact, add to pool if new
     * 
     * @param {Contact} contact
     */ 
    upsertContact(contact) {
        this.addContact(contact);
        this.selectContact(contact.uid);
    }
    
    /**
     * Remove all contacts
     */ 
    clearContacts() {
        this._contactPool = [];
        this._currentContact = undefined;
    }
    
    /**
     * Reset state
     * 
     * @param {bool} preserveContacts
     */ 
    reset(preserveContacts = false) {
        if (!preserveContacts) {
            this._currentContact = undefined;
            this._contactPool = [];
            this._phoneNumber = undefined;
        }
        this._processLog = new ProcessLog();
        this._processing = false;
    }
    
    /**
     * Start process
     */ 
    startProcess() {
        this._processing = true;
    }
    
    /**
     * Serialize state
     * 
     * @returns {object}
     */ 
    serialize() {
        return {
            log: this._processLog.serialize(),
            pool: this._contactPool.map((contact) => contact.serialize()),
            contact: this._currentContact === undefined ? null : this._currentContact.uid,
            number: this._phoneNumber === undefined ? null : this._phoneNumber,
            processing: this._processing,
        };
    }
    
    /**
     * Unserialize state
     * 
     * @param {object} data
     */ 
    static unserialize(data) {
        let state = new ProcessState();
        state.processLog.set(data.log);
        data.pool.forEach((item) => {
            state.addContact(Contact.unserialize(item));
        });
        if (data.contact !== null) {
            state.selectContact(data.contact);
        }
        state.phoneNumber = data.number === null ? undefined : data.number;
        if (data.processing) {
            state.startProcess();
        }
        return state;
    }
    
    /**
     * Check if state is bound to call or enquiry
     * 
     * @returns {bool}
     */ 
    hasContext() {
        return this._processLog.call !== null
            || this._processLog.enquiry !== null;
    }
}

export {
    Contact,
    PatientContact,
    EmployeeContact,
    ProcessState,
};