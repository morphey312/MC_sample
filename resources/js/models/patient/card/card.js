import BaseModel from '@/models/base-model';
import CardSpecialization from './card-specialization';

class PatientCard extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            number: null,
            clinic_id: null,
            patient_id: null,
            specializations: [],
        }
    }
    
    /**
     * @inheritdoc
     */
    mutations() {
        return {
            specializations: (value) => value.map((v) => this.initSubModel(CardSpecialization, v)),
        };
    }
}

export default PatientCard;