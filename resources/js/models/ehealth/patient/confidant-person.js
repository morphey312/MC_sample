import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
    missing,
    assertion,
    after,
    before,
    date, ukrSpelling, phoneNumberCellular, email, length, taxId, unzr
} from '@/services/validation';
import CONSTANT from "@/constants";
import moment from "moment";
import PatientDocument from "@/models/ehealth/patient/document";
import PatientRelationshipDocument from "@/models/ehealth/patient/relationship-document";

class ConfidantPerson extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            relation_type: null,
            ehealth_patient_id: null,
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
            tax_id: null,
            unzr: null,
            patient_documents: [],
            patient_relationship_documents: [],
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            patient_documents: (value) => this.castToInstances(PatientDocument, value),
            patient_relationship_documents: (value) => this.castToInstances(PatientRelationshipDocument, value),
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            relation_type: required,
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
            tax_id: taxId.or(missing),
            unzr: unzr.or(missing.and(assertion(() => {
                return !(_.some(this.patient_documents, ['type', CONSTANT.EHEALTH_PATIENT.DOCUMENTS_TYPE.NATIONAL_ID]) || _.isFilled(this.unzr))
            }))),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/ehealth/patient/confidant-persons',
            fetch: '/api/v1/ehealth/patient/confidant-persons/{id}',
            update: '/api/v1/ehealth/patient/confidant-persons/{id}',
            delete: '/api/v1/ehealth/patient/confidant-persons/{id}',
        }
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
}

export default ConfidantPerson;
