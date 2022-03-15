import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';

class AssignedService extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            service_id: null,
            price_id: null,
            quantity: 1,
            is_free: false,
            patient_id: null,
            assigner_id: null,
            clinic_id: null,
            card_assignment_id: null,
            assigned_quantity: 1,
            cost: 0,
            discount: 0,
            self_cost: 0,
            comment: null,
            appointment_service_count: 0,
            by_policy: false,
            franchise: 0,
            warranter: '',
        };
    }
    
    /** 
     * @inheritdoc
     */
    validation() {
        return {
            service_id: required,
            assigner_id: required,
            patient_id: required,
            clinic_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            delete: '/api/v1/patients/assigned-services/{id}',
        }
    }
}

export default AssignedService;