import BaseModel from '@/models/base-model';
import {
    required,
    date,
    assertion,
} from '@/services/validation';
import CONSTANT from '@/constants';

class IssuedDiscountCard extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            discount_card_type_id: null,
            clinic_id: null,
            number: null,
            issued: null,
            valid_from: '',
            expires: null,
            comment: '',
            patients: [],
            type: {
                name: '',
                discount_percent: 0,
            },
            owner: null,
            used_in_appointments: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            discount_card_type_id: required,
            clinic_id: required,
            issued: required.and(date),
            valid_from: required.and(assertion((val) => {
                return val > this.issued || val == this.issued;
            })),
            expires: required.and(assertion((val) => {
                return val > this.issued || val == this.issued;
            })),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            isDeleteable: '/api/v1/patients/issued-discount-cards/{id}/isDeleteable'
        };
    }

    /**
     * @inheritdoc
     */
    options() {
        return {
            methods: {
                isDeleteable: 'GET',
            }
        }
    }

    /**
     * Get related models
     *
     * @returns Promise
     */
    isDeleteable() {
        let method = this.getOption('methods.isDeleteable');
        let route  = this.getRoute('isDeleteable');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data;

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response);
        });
    }

    /**
     * Add patient to card holders
     *
     * @param int patientId
     */
    addHolder(patientId) {
        if (this.patients.length === 0) {
            return this.addPatient(patientId, true);
        }

        let patient = this.patients.find(item => item.patient_id == patientId);
        if (_.isEmpty(patient)) {
            return this.addPatient(patientId);
        }
    }

    /**
     * Add patient to card patients
     *
     * @param int patientId
     * @param bool isOwner
     */
    addPatient(patientId, isOwner = false) {
        this.patients.push({
            patient_id: patientId,
            disabled: false,
            is_owner: isOwner,
        });
    }
    get users_count() {
        return this.patients.length;
    }
    get active_users_count() {
        return this.patients.filter((el) => el.disabled == false).length;
    }
}

export default IssuedDiscountCard;
