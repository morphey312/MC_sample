import BaseModel from '@/models/base-model';
import Patient from '@/models/patient';
import ProcessLog from '@/models/calls/process-log';
import {dateFormat} from '@/services/format';
import {
    required,
    date
} from '@/services/validation';

class WaitListRecord extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            period_from: null,
            period_to: null,
            found_date: null,
            patient_id: null,
            clinic_id: null,
            doctor_id: null,
            specialization_id: null,
            operator_id: null,
            call_id: null,
            cancel_reason: null,
            patient: null,
            prepayment_service: null,
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            patient: (value) => this.castToInstance(Patient, value, true),
            process: (value) => this.castToInstance(ProcessLog, value, true),
        };
    }
    /**
     * @inheritdoc
     */
    validation() {
        return {
            period_from: required.and(date),
            period_to: required.and(date),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/wait-list-records',
            fetch: '/api/v1/wait-list-records/{id}',
            update: '/api/v1/wait-list-records/{id}',
        }
    }

    /**
     * Get patient phone number
     *
     * @returns {string}
     */
    get phone_number() {
        return this.patient.primary_phone_number;
    }

    /**
     * Get patient card number
     *
     * @returns {string}
     */
    get card_number() {
        return this.patient.card_number;
    }

    /**
     * Get patient email
     *
     * @returns {string}
     */
    get email() {
        return this.patient.email;
    }

    /**
     * Get period_range
     *
     * @returns {string}
     */
    get period_range() {
        return `${dateFormat(this.period_from)} - ${dateFormat(this.period_to)}`;
    }

    /**
     * Get clinic name
     */
    get clinic_name() {
        return this.attributes.clinic_name;
    }

    /**
     * Get card number
     *
     * @returns string
     */
    get card_number() {
        return this.attributes.card_number;
    }

    /**
     * Get specialization name
     */
    get specialization_name() {
        return this.attributes.specialization_name;
    }

    /**
     * Get doctor name
     */
    get doctor_name() {
        return this.attributes.doctor_name;
    }

    /**
     * Get name
     */
    get name() {
        return this.attributes.name;
    }
}

export default WaitListRecord;
