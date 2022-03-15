import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * Workspace model
 */
class Workspace extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Workspace';
    }
    
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: '',
            has_day_sheet: false,
            is_active: false,
            is_operational: false,
            is_hospital_room: false,
        }
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/workspaces',
            fetch: '/api/v1/workspaces/{id}',
            update: '/api/v1/workspaces/{id}',
            delete: '/api/v1/workspaces/{id}',
        }
    }

     /**
     * Get names of clinics employee belongs to
     * 
     * @returns {array}
     */ 
    get clinic_names() {
        return this.workspace_clinics 
            ? this.workspace_clinics
                .map((clinic) => clinic.clinic_name)
            : [];
    }

    /**
     * Get specializations of employee accross all clinics
     * 
     * @returns {array}
     */ 
    get specialization_names() {
        return this.workspace_clinics 
            ? _.uniq(
                _.flatten(
                    this.workspace_clinics
                        .map((clinic) => {
                            return clinic.specialization_names;
                        })
                    )
                )
            : [];
    }
}

export default Workspace;