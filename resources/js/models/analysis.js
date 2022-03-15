import BaseModel from '@/models/base-model';
import Price from '@/models/price';
import {
    required,
    maxlen,
    TEXT_MAX_LEN,
    requiredArray
} from '@/services/validation';

/**
 * Analysis model
 */
class Analysis extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Analysis';
    }
    
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: '',
            laboratory_code: '',
            laboratory_id: null,
            description: null,
            disabled: false,
            candidate_id: null,
            clinics: [],
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            prices: (value) => this.castToInstances(Price, value),
        };
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(TEXT_MAX_LEN)),
            laboratory_id: required,
            clinics: requiredArray,
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/analyses',
            fetch: '/api/v1/analyses/{id}',
            update: '/api/v1/analyses/{id}',
            delete: '/api/v1/analyses/{id}',
        }
    }
    
    /**
     * Get clinic IDs
     * 
     * @returns {array}
     */ 
    get clinic_ids() {
        return this.clinics.map((clinic) => clinic.clinic_id);
    }
}

export default Analysis;