import BaseModel from '@/models/base-model';
import {
    required,
    date,
    boolean,
} from '@/services/validation';

class Condition extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            encounter_id: null,
            onset_date: null,
            primary_source: false,
            severity: null,
            clinical_status: null,
            verification_status: null,
            body_sites: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            onset_date: required.and(date),
            encounter_id: required,
            primary_source: boolean,
            clinical_status: required,
            verification_status: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/ehealth/encounter/conditions',
            fetch: '/api/v1/ehealth/encounter/conditions/{id}',
            update: '/api/v1/ehealth/encounter/conditions/{id}',
            delete: '/api/v1/ehealth/encounter/conditions/{id}',
        }
    }
}

export default Condition;
