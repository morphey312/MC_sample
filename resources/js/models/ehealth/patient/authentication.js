import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
    phoneNumberCellular,
    assertion,
    missing
} from '@/services/validation';
import CONSTANT from "@/constants";
import axios from "axios";

class PatientAuthentication extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            ehealth_patient_id: null,
            ehealth_id: null,
            phone_number: null,
            type: null,
            value: null,
            alias: null,
            urgent: null,
            action: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            phone_number: phoneNumberCellular.or(missing.and(assertion(() => {
                return this.type !== CONSTANT.EHEALTH_PATIENT.AUTHENTICATION_TYPE.OTP || this.action !== CONSTANT.EHEALTH_PATIENT.AUTHENTICATION_METHODS.INSERT;
            }))),
            value: required.and(maxlen(STRING_MAX_LEN)).or(missing.and(assertion(() => {
                return this.type !== CONSTANT.EHEALTH_PATIENT.AUTHENTICATION_TYPE.THIRD_PERSON;
            }))),
            alias: required.and(maxlen(TEXT_MAX_LEN)).or(missing.and(assertion(() => {
                return this.type !== CONSTANT.EHEALTH_PATIENT.AUTHENTICATION_TYPE.THIRD_PERSON;
            }))),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/ehealth/patient/authentications',
            fetch: '/api/v1/ehealth/patient/authentications/{id}',
            update: '/api/v1/ehealth/patient/authentications/{id}',
            delete: '/api/v1/ehealth/patient/authentications/{id}',
            findVerificationsByPhoneNumber: '/api/v1/ehealth/patient/authentications/find-verifications/{phoneNumber}',
            approve: '/api/v1/ehealth/patient/authentications/{id}/approve',
        }
    }

    /**
     * find verifications by phone number
     *
     * @returns {Promise}
     */
    findVerificationsByPhoneNumber() {
        let method = 'GET';
        let route = this.getRoute('findVerificationsByPhoneNumber');
        let url = this.getURL(route, {phoneNumber: this.phone_number});

        return this.getRequest({method, url}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
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
}

export default PatientAuthentication;
