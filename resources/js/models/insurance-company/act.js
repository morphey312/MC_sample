import BaseModel from '@/models/base-model';
import CONSTANTS from '@/constants';
import {
    required,
    maxlen,
    TEXT_MAX_LEN,
} from '@/services/validation';

class InsuranceCompanyAct extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            insurance_company_id: null,
            clinic_id: null,
            amount: 0,
            number: null,
            comment: null,
            status: CONSTANTS.INSURANCE_ACT.STATUSES.CREATED,
            payment_status: CONSTANTS.INSURANCE_ACT.STATUSES.NEW,
            payment_date: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            insurance_company_id: required,
            clinic_id: required,
            comment: maxlen(TEXT_MAX_LEN),
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/insurance/company-acts',
            fetch: '/api/v1/insurance/company-acts/{id}',
            update: '/api/v1/insurance/company-acts/{id}',
            printed: '/api/v1/insurance/company-acts/{id}/printed',
        }
    }

    /**
     * Send printed
     */
    printed() {
        let method = this.getOption(`methods.create`);
        let route  = this.getRoute('printed');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);

        return this.getRequest({method, url}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /** 
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        let services = _.get(this, 'services', false);
        if (services !== false && services.length) {
            attributes.act_services = services;
        }
        return attributes;
    }
}

export default InsuranceCompanyAct;