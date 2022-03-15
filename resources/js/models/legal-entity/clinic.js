import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';

class LegalEntityClinic extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            legal_entity_id: null,
            clinic_id: null,
            agreement: '',
            agreement_active: true,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            legal_entity_id: required,
            clinic_id: required,
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/legal-entity/clinics',
            fetch: '/api/v1/legal-entity/clinics/{id}',
            update: '/api/v1/legal-entity/clinics/{id}',
            delete: '/api/v1/legal-entity/clinics/{id}',
        }
    }
}

export default LegalEntityClinic;