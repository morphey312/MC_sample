import BaseModel from '@/models/base-model';
import {
    required,
    requiredArray,
    missing,
    gte,
    assertion,
} from '@/services/validation';

class WorkspaceClinic extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            workspace_id: null,
            clinic_id: null,
            sip_number: '',
            appointment_duration: 0,
            specializations: [],
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            clinic_id: required,
            specializations: requiredArray.or(missing),
            appointment_duration: gte(5).or(assertion(() => {
                return _.get(this._parent, 'has_day_sheet', false) === false;
            })),
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/workspace/clinics',
            fetch: '/api/v1/workspace/clinics/{id}',
            update: '/api/v1/workspace/clinics/{id}',
            delete: '/api/v1/workspace/clinics/{id}',
        }
    }
}

export default WorkspaceClinic;