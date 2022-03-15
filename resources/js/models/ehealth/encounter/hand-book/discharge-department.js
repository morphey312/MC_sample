import BaseModel from '@/models/base-model';

class EhealthDischargeDepartment extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            code: null,
        };
    }
}

export default EhealthDischargeDepartment;
