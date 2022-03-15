import BaseModel from '@/models/base-model';
import {
    required,
    dateformat,
} from '@/services/validation';

/**
 * Operator bonus model
 */
class DoctorIncomePlan extends BaseModel
{
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            employee_id: null,
            clinic_id: null,
            specialization_id: null,
            plan_service_mark: null,
            year: null,
            january: 0,
            february: 0,
            march: 0,
            april: 0,
            may: 0,
            june: 0,
            july: 0,
            august: 0,
            september: 0,
            october: 0,
            november: 0,
            december: 0,
        }
    }

    /** 
     * @inheritdoc
     */
    validation() {
        return {
            employee_id: required,
            clinic_id: required,
            specialization_id: required,
            year: required.and(dateformat('yyyy')),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/doctor-income-plans/',
            fetch: '/api/v1/employees/doctor-income-plans/{id}',
            update: '/api/v1/employees/doctor-income-plans/{id}',
        }
    }
}

export default DoctorIncomePlan;