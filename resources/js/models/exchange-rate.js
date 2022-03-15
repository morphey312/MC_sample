import BaseModel from '@/models/base-model';

/**
 * ExchangeRate model
 */
class ExchangeRate extends BaseModel 
{
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            value: null,
            date: null,
            code: null,
        }
    }
}

export default ExchangeRate;