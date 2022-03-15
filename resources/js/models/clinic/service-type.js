import BaseModel from '@/models/base-model';
import ServiceUnavailable from './service-unavailable';
import {
    maxlen,
    required,
    TEXT_MAX_LEN,
} from '@/services/validation';

class ServiceType extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            clinic_id: null,
            speciality_type_id: null,
            providing_condition: null,
            comment: null,
            available_time: null,
            not_available: [],
            is_active: 1,
            ehealth_id: null,
            active_in_ehealth: false,
            ehealth_request: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            speciality_type_id: required,
            providing_condition: required,
            comment: maxlen(TEXT_MAX_LEN),
            is_active: required,
        }
    }

    /** 
     * @inheritdoc
     */
    mutations() {
        return {
            not_available: (value) => _.isArray(value) ? value.map((unavailable) => this.initSubModel(ServiceUnavailable, unavailable)) : [],
        }
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/clinics/service-types',
            fetch: '/api/v1/clinics/service-types/{id}',
            update: '/api/v1/clinics/service-types/{id}',
            delete: '/api/v1/clinics/service-types/{id}',
        }
    }
}

export default ServiceType;