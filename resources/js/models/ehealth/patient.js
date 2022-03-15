import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    ukrSpelling,
    email,
    phoneNumberCellular,
    assertion,
    missing,
    taxId,
    unzr,
    length,
} from '@/services/validation';
import moment from 'moment';
import EhealthAddress from '@/models/ehealth/address';
import CONSTANT from '@/constants';
import PatientDocument from '@/models/ehealth/patient/document';
import PatientAuthentication from '@/models/ehealth/patient/authentication';
import ConfidantPerson from '@/models/ehealth/patient/confidant-person';

class Patient extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Patient';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            patient_type: 'patient',
            patient_id: null,
            first_name: null,
            last_name: null,
            second_name: null,
            birth_country: null,
            birth_settlement: null,
            birth_date: null,
            gender: null,
            phone_number: '+38',
            phone_type: null,
            email: null,
            preferred_way_communication: null,
            secret: null,
            no_tax_id: false,
            tax_id: null,
            unzr: null,
            address: {},
            trusted_person_last_name: null,
            trusted_person_first_name: null,
            trusted_person_second_name: null,
            trusted_person_phone_type: null,
            trusted_person_phone_number: '+38',
            incompetent: false,
            patient_documents: [],
            patient_authentications: [],
            patient_relationship_documents: [],
            process_disclosure_data_consent: false,
            content: false,
            urgent: false,
            chanel: false,
            patient_signed: false,
            authorize_with: [],
            confidant_person: {},
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            patient_documents: (value) => this.castToInstances(PatientDocument, value),
            patient_authentications: (value) => this.castToInstances(PatientAuthentication, value),
            confidant_person: (value) => this.castToInstance(ConfidantPerson, value),
            address: (val) => this.initSubModel(EhealthAddress, val),
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            first_name: required.and(maxlen(STRING_MAX_LEN).and(ukrSpelling)),
            last_name: required.and(maxlen(STRING_MAX_LEN).and(ukrSpelling)),
            second_name: maxlen(STRING_MAX_LEN).and(ukrSpelling),
            birth_country: required,
            birth_settlement: required,
            birth_date: required,
            gender: required,
            phone_number: required.and(phoneNumberCellular),
            phone_type: required,
            email: email,
            preferred_way_communication: required,
            secret: required.and(maxlen(STRING_MAX_LEN).and(length(6))),
            tax_id: taxId.or(missing.and(assertion(() => {
                return this.no_tax_id || !(this.age >= 14)
            }))),
            no_tax_id: required,
            unzr: unzr.or(missing.and(assertion(() => {
                return !(_.some(this.patient_documents, ['type', CONSTANT.EHEALTH_PATIENT.DOCUMENTS_TYPE.NATIONAL_ID]) || _.isFilled(this.unzr))
            }))),
            address: (value) => this.validateSubmodel(value, null, ['country_code', 'region', 'settlement_id']),
            trusted_person_last_name: required.and(maxlen(STRING_MAX_LEN).and(ukrSpelling)),
            trusted_person_first_name: required.and(maxlen(STRING_MAX_LEN).and(ukrSpelling)),
            trusted_person_second_name: maxlen(STRING_MAX_LEN).and(ukrSpelling),
            trusted_person_phone_number: required.and(phoneNumberCellular),
            trusted_person_phone_type: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/ehealth/patients',
            fetch: '/api/v1/ehealth/patients/{id}',
            update: '/api/v1/ehealth/patients/{id}',
            approve: '/api/v1/ehealth/patients/{id}/approve',
            sign: '/api/v1/ehealth/patients/{id}/sign',
            resendOtp: '/api/v1/ehealth/patients/{id}/resend-otp',
            getSignRequest: '/api/v1/ehealth/patients/{id}/get-sign-request',
        }
    }

    /**
     * Send contract to be approved
     *
     * @returns {Promise}
     */
    approve(code, documents) {
        let method = 'POST';
        let route = this.getRoute('approve');
        let url = this.getURL(route, {id: this.id});
        let data = {
            documents_for_upload: documents,
            verification_code: code ? parseInt(code) : null,
        };

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Send contract to be approved
     *
     * @returns {Promise}
     */
    resendOtp() {
        let method = 'POST';
        let route = this.getRoute('resendOtp');
        let url = this.getURL(route, {id: this.id});

        return this.getRequest({method, url}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Send contract to be approved
     *
     * @returns {Promise}
     */
    getMsp() {
        let method = 'GET';
        let route = this.getRoute('getMsp');
        let url = this.getURL(route, {id: this.id});

        return this.getRequest({method, url}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Send contract to be approved
     *
     * @returns {Promise}
     */
    getSignRequest() {
        let method = 'GET';
        let route = this.getRoute('getSignRequest');
        let url = this.getURL(route, {id: this.id});

        return this.getRequest({method, url}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Sign contract
     *
     * @returns {Promise}
     */
    sign(signed) {
        let method = 'POST';
        let route = this.getRoute('sign');
        let url = this.getURL(route, {id: this.id});
        let data = {signed};

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Get patient age
     *
     * @returns {number}
     */
    get age() {
        if (this.birth_date) {
            return moment().diff(this.birth_date, 'years');
        }
        return 0;
    }

    /**
     * Get patient needed in confidant_person
     *
     * @returns {number}
     */
    get need_confidant_person() {
        if (this.age <= 14 || this.incompetent) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get patient full name
     *
     * @returns {string}
     */
    get full_name() {
        return [
            this.last_name,
            this.first_name,
            this.second_name,
        ]
            .filter(_.isFilled)
            .join(' ');
    }

    /**
     * Get patient full name
     *
     * @returns {string}
     */
    get trusted_person_full_name() {
        return [
            this.trusted_person_last_name,
            this.trusted_person_first_name,
            this.trusted_person_second_name,
        ]
            .filter(_.isFilled)
            .join(' ');
    }
}

export default Patient;
