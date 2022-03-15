import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * NumberingKind model
 */
class NumberingKind extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'DiscountCardType_NumberingKind';
    }
    
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: '',
            unique: true,
            clinics: [],
        }
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/discount-card-types/numbering-kinds',
            fetch: '/api/v1/discount-card-types/numbering-kinds/{id}',
            update: '/api/v1/discount-card-types/numbering-kinds/{id}',
            delete: '/api/v1/discount-card-types/numbering-kinds/{id}',
        }
    }
}

export default NumberingKind;