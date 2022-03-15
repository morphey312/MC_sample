import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';

/**
 * AppointmentStatus model
 */
class Delay extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Appointment_Delay';
    }
    
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            delay_reason_id: null,
            appointment_id: null,
            comment: null,
            duration: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            delay_reason_id: required,
            appointment_id: required,
            duration: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/appointments/delays',
        }
    }
}

export default Delay;