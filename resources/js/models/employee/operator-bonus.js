import BaseModel from '@/models/base-model';
import {
    required,
    numeric,
    gte,
    requiredArray,
} from '@/services/validation';

/**
 * Operator bonus model
 */
class OperatorBonus extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Employee_OperatorBonus';
    }
    
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            operator_id: null,
            evaluation: 0,
            clinics: [],
        }
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            operator_id: required,
            evaluation: required.and(numeric).and(gte(0)),
            clinics: requiredArray,
        };
    }
}

export default OperatorBonus;