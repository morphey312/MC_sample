import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';

/**
 * AppointmentStatus model
 */
class LockLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Log_Lock';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            start: null,
            end: null,
            day_sheet_id: null,
            daysheet: null,
            lock_id: null,
            comment: null,
            comment_off: null,
            type: null,
            reason_id: null,
            reason_off_id: null,
            doctor: null,
            employee_id: null,
            employee_off_id: null,
            appointment_id: null,
            status: false,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            day_sheet_id: required,
            reason_id: required,
            employee_id: required,
        };
    }


}

export default LockLog;
