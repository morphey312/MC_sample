import BaseModel from '@/models/base-model';
import moment from 'moment';
import {
    required,
    date,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

class InsurancePolicy extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            patient_id: null,
            insurance_company_id: null,
            number: null,
            expires: null,
            comment: '',
        };
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            patient_id: required,
            insurance_company_id: required,
            number: required.and(maxlen(STRING_MAX_LEN)),
            expires: required.and(date),
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            isDeleteable: '/api/v1/patients/insurance-policies/{id}/isDeleteable'
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
     * Get is policy valid
     */
    get is_valid() {
        return moment().isSameOrBefore(this.expires, 'day');
    }
}

export default InsurancePolicy;