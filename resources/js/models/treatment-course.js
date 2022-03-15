import BaseModel from '@/models/base-model';
import {
    required,
} from '@/services/validation';
import Patient from '@/models/patient';
import TreatmentCourseDocument from '@/models/treatment-course/document';
import {timeFormat} from '@/services/format';

/**
 * TreatmentCourse model
 */

class TreatmentCourse extends BaseModel {
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            start: null,
            end: null,
            patient_id: null,
            doctor_id: null,
            card_specialization_id: null,
            is_surgery: false,
            number: null,
        }
    }

     /**
     * @inheritdoc
     */
    mutations() {
        return {
            patient: (value) => this.castToInstance(Patient, value),
            documents: (value) => this.castToInstances(TreatmentCourseDocument, value),
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            start: required,
            patient_id: required,
            doctor_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/treatment-courses',
            fetch: '/api/v1/treatment-courses/{id}',
            update: '/api/v1/treatment-courses/{id}',
            delete: '/api/v1/treatment-courses/{id}',
        }
    }

    /** 
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        
        if (_.get(this, 'record_card_specialization_id', false) !== false) {
            attributes.record_card_specialization_id = this.record_card_specialization_id;
        }

        if (_.get(this, 'appointment_id', false) !== false) {
            attributes.appointment_id = this.appointment_id;
        }
        
        return attributes;
    }

    /**
     * Get first appointment in course start
     * 
     * @returns {string}
     */
    get start_time() {
        if (this.appointments.length === 0) {
            return '';
        }
        let start = _.orderBy(this.appointments, ['start'], ['asc'])[0].start;
        return timeFormat(start);
    }

    /**
     * Get treatment refuse reason
     * 
     * @returns {string|null}
     */
    get refuse_reason() {
        if (this.appointments.length === 0) {
            return '';
        }
        let appointment = this.appointments.find(appointment => _.isFilled(appointment.rejection_reason));
        return appointment ? appointment.rejection_reason : null;
    }

    /**
     * Get services
     * 
     * @returns {string}
     */
    get services() {
        if (this.appointments.length === 0) {
            return '';
        }
        let services = [];
        this.appointments.forEach(appointment => {
            appointment.services.forEach(service => services.push(service.name));
        });
        return services.join(', ');
    }

    /**
     * Get patient stationar status
     * 
     * @returns {string}
     */
    get patient_status() {
        return this.end === null ? __('В стационаре') : __('Выписан');
    }

    /**
     * Get patient reffered
     * 
     * @returns {string}
     */
    get reffered() {
        return __('самообращение');
    }
    
    /**
     * Get patient full_name
     * 
     * @returns {string}
     */
    get patient_full_name() {
        return this.patient.full_name;
    }

    /**
     * Get patient birthday
     * 
     * @returns {string}
     */
    get patient_birthday() {
        return this.patient.birthday;
    }

    /**
     * Get patient full_address
     * 
     * @returns {string}
     */
    get patient_full_address() {
        return this.patient.full_address;
    }

    /**
     * Get patient card_number
     * 
     * @returns {string}
     */
    get patient_card_number() {
        return this.patient.card_number;
    }
    
    /**
     * Get treatment event
     * 
     * @returns {string}
     */
    get treatment_event() {
        let consultation = __('Проконсультирован'); 
        if (this.appointments.length === 0) {
            return consultation;
        }
        
        let surgeryIndex = this.appointments.findIndex(appointment => {
            return appointment.doctor && appointment.doctor.is_operational === true;
        });
        if (surgeryIndex !== -1) {
            return __('Прооперирован');
        }

        let stationarIndex = this.appointments.findIndex(appointment => {
            return appointment.doctor && appointment.doctor.is_hospital_room === true;
        });
        if (stationarIndex !== -1) {
            return __('Госпитализирован');
        }
        return consultation;
    }

    /**
     * Get last diagnosis
     * 
     * @returns {string}
     */
    get last_diagnosis() {
        let appointment = _.orderBy(this.appointments, 'date', 'desc')
            .find(item => item.diagnosis.length != 0);
        return appointment ? appointment.diagnosis.join(', ') : '';
    }

    /**
     * Get last diagnosis icd
     * 
     * @returns {string}
     */
    get last_diagnosis_icd() {
        let appointment = _.orderBy(this.appointments, 'date', 'desc')
            .find(item => item.diagnosis_icd.length != 0);
        return appointment ? _.uniq(appointment.diagnosis_icd).join(', ') : '';
    }
}

export default TreatmentCourse;