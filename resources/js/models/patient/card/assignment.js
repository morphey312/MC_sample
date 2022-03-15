import BaseModel from '@/models/base-model';

class Assignment extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            type: null,
            card_specialization_id: null,
            appointment_id: null,
        }
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/assignments',
            fetch: '/api/v1/patients/cards/assignments/{id}',
            update: '/api/v1/patients/cards/assignments/{id}',
            delete: '/api/v1/patients/cards/assignments/{id}',
        }
    }

    /** 
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        
        if (_.get(this, 'type', false) !== false) {
            let assignments = this.type;
            attributes[assignments] = this[assignments];
        }

        if (_.get(this, 'additional_type', false) !== false) {
            let assignments = this.additional_type;
            attributes[assignments] = this[assignments];
        }
        return attributes;
    }
}

export default Assignment;