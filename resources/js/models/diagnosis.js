import BaseModel from '@/models/base-model';

class Diagnosis extends BaseModel {
	/** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            code: '',
            description: '',
        }
    }
}

export default Diagnosis;