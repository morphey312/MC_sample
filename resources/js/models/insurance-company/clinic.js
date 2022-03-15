import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';

class InsuranceCompanyClinic extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            insurance_company_id: null,
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
            insurance_company_id: required,
            clinic_id: required,
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/insurance-company/clinics',
            fetch: '/api/v1/insurance-company/clinics/{id}',
            update: '/api/v1/insurance-company/clinics/{id}',
            delete: '/api/v1/insurance-company/clinics/{id}',
        }
    }
}

export default InsuranceCompanyClinic;