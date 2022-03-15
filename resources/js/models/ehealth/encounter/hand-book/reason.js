import BaseModel from '@/models/base-model';

class EhealthReason extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            code: null,
            encounter: null,
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            name: (value) => {return value + ' ' + this.code},
        }
    }
}

export default EhealthReason;
