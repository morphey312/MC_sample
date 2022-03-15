import BaseModel from '@/models/base-model';

class PatientRegistration extends BaseModel 
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Patient_Registration';
    }
    
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            firstname: null,
            middlename: null,
            lastname: null,
            birthday: null,
            phone: null,
            email: null,
            status: null,
        };
    }
    
    /** 
     * @inheritdoc
     */
    validation() {
        return {
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/registrations',
            fetch: '/api/v1/patients/registrations/{id}',
            update: '/api/v1/patients/registrations/{id}',
            delete: '/api/v1/patients/registrations/{id}',
        }
    }

    /**
     * Get applicant full name
     *
     * @returns {string}
     */
    get full_name() {
        return [
            this.lastname,
            this.firstname,
            this.middlename,
        ]
            .filter(_.isFilled)
            .join(' ');
    }
}

export default PatientRegistration;