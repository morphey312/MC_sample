import BaseModel from '@/models/base-model';
import {
    date,
} from '@/services/validation';

class Price extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            price_id: null,
            price_agreement_act_id: null,
            recommended_cost: null,
            currency: null,
            clinics: [],
        }
    }
    /**
     * @inheritdoc
     */
     routes() {
        return {
            delete: 'api/v1/price-agreement-act/price/{id}',
     }
    }
}

export default Price;
