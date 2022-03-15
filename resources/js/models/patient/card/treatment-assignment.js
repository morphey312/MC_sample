import BaseModel from '@/models/base-model';

class TreatmentAssignment extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            initial: false,
            card_specialization_id: null,
            appointment_id: null,
            treatment_course_id: null,
        }
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/treatment-assignments',
            fetch: '/api/v1/patients/cards/treatment-assignments/{id}',
            update: '/api/v1/patients/cards/treatment-assignments/{id}',
            delete: '/api/v1/patients/cards/treatment-assignments/{id}',
        }
    }

    /** 
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        
        if (_.get(this, 'assignments', false) !== false) {
            attributes[assignments] = this.assignments;
        }
        return attributes;
    }
}

export default TreatmentAssignment;