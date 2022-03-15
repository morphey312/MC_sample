import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';

class SpecializationClinic extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            specialization_id: null,
            clinic_id: null,
            first_patient_appointment_limit: 20,
            status: 1,
            days_since_last_visit: null,
            show_days_since_message: false,
            money_reciever_id: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            clinic_id: required,
            specialization_id: required,
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/specialization/clinics',
            fetch: '/api/v1/specialization/clinics/{id}',
            update: '/api/v1/specialization/clinics/{id}',
            delete: '/api/v1/specialization/clinics/{id}',
        }
    }
}

export default SpecializationClinic;