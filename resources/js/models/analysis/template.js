import BaseModel from '@/models/base-model';
import {
    required,
    requiredArray,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

class AnalysisTemplate extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Analysis_Template';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            file_id: null,
            stamp_file_id: null,
            clinics: [],
            analyses: [],
            laboratories: [],
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            file_id: required,
            clinics: requiredArray,
            analyses: requiredArray,
            laboratories: requiredArray,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/analyses/templates',
            fetch: '/api/v1/analyses/templates/{id}',
            update: '/api/v1/analyses/templates/{id}',
            delete: '/api/v1/analyses/templates/{id}',
        }
    }
}

export default AnalysisTemplate;
