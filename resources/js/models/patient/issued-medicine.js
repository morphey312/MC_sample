import BaseModel from '@/models/base-model';

class IssuedMedicine extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            assigned_medicine_id: null,
            quantity: 0,
        };
    }
}

export default IssuedMedicine;