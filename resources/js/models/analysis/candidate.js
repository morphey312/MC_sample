import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

class Candidate extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Analyses_Candidate';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            code: null,
            lab_analysis_id: null,
            laboratories: [],
     
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            code: required,
            lab_analysis_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: 'api/v1/analyses/candidates',
            fetch: '/api/v1/analyses/candidates/{id}',
            update: '/api/v1/analyses/candidates/{id}',
            delete: '/api/v1/analyses/candidates/{id}',
        }
    }
}

export default Candidate;
