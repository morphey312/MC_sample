import BaseModel from '@/models/base-model';
import Patient from '@/models/patient';
import {
    required,
    date,
} from '@/services/validation';
import CONSTANTS from '@/constants';

class Occupation extends BaseModel {

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            date_from: null,
            date_to: null,
            start: null,
            end: null,
            status: CONSTANTS.PLACE_OCCUPATION.STATUS.RESERVED,
            room_id: null,
            place_id: null,
            patient_id: null,
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            patient: (value) => this.castToInstance(Patient, value),
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            date_from: required.and(date),
            date_to: required.and(date),
            start: required,
            end: required,
            room_id: required,
            place_id: required,
            patient_id: required,
        };
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/departments/rooms/occupations',
            fetch: '/api/v1/departments/rooms/occupations/{id}',
            update: '/api/v1/departments/rooms/occupations/{id}',
            delete: '/api/v1/departments/rooms/occupations/{id}',
        }
    }

    /**
     * Get place attribute
     * 
     * @returns {mixed}
     */
    get place() {
        return this.attributes.place;
    }

    /**
     * Get patient attribute
     * 
     * @returns {mixed}
     */
    get patient() {
        return this.attributes.patient;
    }
}

export default Occupation;