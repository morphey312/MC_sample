import BaseModel from '@/models/base-model';
import MspSubcontractor from './contract/contractor';
import {
    required,
    requiredArray,
    maxlen,
    ukrSpelling,
    ehealthContractNumber,
    mfo,
    missing,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
} from '@/services/validation';

/**
 * MspContract model
 */
class MspContract extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'MspContract';
    }

    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
	        msp_id: null,
	        type: null,
	        contractor_base: null,
	        ehealth_id: null,
	        ehealth_request_id: null,
            payer_account_number: null,
            payer_mfo: null,
	        payer_bank: null,
	        start_date: null,
	        end_date: null,
	        form_type: null,
	        contract_number: null,
	        medical_program: {
                id: null,
            },
	        statute_id: null,
	        additional_document_id: null,
            signed_contract_content: null,
            clinics: [],
            subcontractors: [],
            ehealth_request: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            type: required,
            contractor_base: required.and(maxlen(TEXT_MAX_LEN)).and(ukrSpelling),
            payer_account_number: required.and(maxlen(STRING_MAX_LEN)),
            payer_mfo: required.and(mfo),
            payer_bank: required.and(maxlen(STRING_MAX_LEN)),
            form_type: required,
            contract_number: ehealthContractNumber.and(maxlen(STRING_MAX_LEN)).or(missing),
            statute_id: required,
            additional_document_id: required,
            clinics: requiredArray,
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            subcontractors: (value) => _.isArray(value) ? value.map((contractor) => this.initSubModel(MspSubcontractor, contractor)) : [],
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/msp/contracts',
            fetch: '/api/v1/msp/contracts/{id}',
            update: '/api/v1/msp/contracts/{id}',
            delete: '/api/v1/msp/contracts/{id}',
            approve: '/api/v1/msp/contracts/{id}/approve',
            sign: '/api/v1/msp/contracts/{id}/sign',
        }
    }

    /**
     * Send contract to be approved
     * 
     * @returns {Promise}
     */
    approve() {
        let method = 'POST';
        let route = this.getRoute('approve');
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
}

export default MspContract;