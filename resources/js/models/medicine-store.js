import BaseModel from '@/models/base-model';

/**
 * MedicineStore model
 */
class MedicineStore extends BaseModel
{
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            store_uid: '',
            code: '',
            description: '',
            firm_uid: '',
            firm_description: '',
        }
    }
}

export default MedicineStore;