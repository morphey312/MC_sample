import BaseModel from '@/models/base-model';
import {
    required,
    numeric,
    gte,
} from '@/services/validation';

/**
 * CallRequest model
 */
class BonusNorm extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Clinic_BonusNorm';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            clinic_id: null,
            appointment_norm: 0,
            income_norm: 0,
            night_repeated_patient: 0,
            day_repeated_patient: 0,
            night_post_call: 0,
            day_post_call: 0,
            mistakes_norm: 0,
            evaluation_norm: 100,
            rate_minimum: 0,
            rate_medium: 0,
            rate_maximum: 0,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            clinic_id: required,
            appointment_norm: required.and(numeric).and(gte(0)),
            income_norm: required.and(numeric).and(gte(0)),
            night_repeated_patient: required.and(numeric).and(gte(0)),
            day_repeated_patient: required.and(numeric).and(gte(0)),
            night_post_call: required.and(numeric).and(gte(0)),
            day_post_call: required.and(numeric).and(gte(0)),
            mistakes_norm: required.and(numeric).and(gte(0)),
            evaluation_norm: required.and(numeric).and(gte(0)),
            rate_minimum: required.and(numeric).and(gte(0)),
            rate_medium: required.and(numeric).and(gte(0)),
            rate_maximum: required.and(numeric).and(gte(0)),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/bonus-norms',
            fetch: '/api/v1/bonus-norms/{id}',
            update: '/api/v1/bonus-norms/{id}',
            delete: '/api/v1/bonus-norms/{id}',
        }
    }
}

export default BonusNorm;
