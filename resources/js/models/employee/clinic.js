import BaseModel from '@/models/base-model';
import Doctor from './doctor';
import CONSTANT from '@/constants';
import {
    required,
    requiredArray,
    assertion,
    numeric,
    gte,
    lte,
    missing,
} from '@/services/validation';

class EmployeeClinic extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            employee_id: null,
            clinic_id: null,
            status: CONSTANT.EMPLOYEE.STATUSES.WORKING,
            position_id: null,
            is_primary: false,
            specializations: [],
            doctor: {},
            sip_number: null,
            sip_password: null,
            date_start_working: null,
            date_end_working: null,
            can_recieve_transfer: false,
            enquiry_categories: []
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            clinic_id: required,
            status: required,
            position_id: required,
            specializations: requiredArray.or(assertion(() => _.get(this, 'position.has_specialization', false) === false)),
            doctor: (value) => this.validateSubmodel(value, () => _.get(this, 'position.is_doctor', false)),
            sip_number: [
                required.or(assertion(() => {
                    return _.get(this, 'position.has_voip', false) === false;
                })),
                numeric.and(gte(101)).and(lte(9999)).or(missing),
            ],
            sip_password: required.or(assertion(() => {
                return _.get(this, 'position.has_voip', false) === false || !this.isNew();
            })),
        };
    }
    
    /**
     * @inheritdoc
     */
    mutations() {
        return {
            doctor: (value) => this.initSubModel(Doctor, value),
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/clinics',
            fetch: '/api/v1/employees/clinics/{id}',
            update: '/api/v1/employees/clinics/{id}',
            delete: '/api/v1/employees/clinics/{id}',
        }
    }
    
    /** 
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        
        if (_.get(this, 'position.has_specialization', false) === false) {
            delete attributes.specializations;
        }
        if (_.get(this, 'position.has_voip', false) === false || _.isVoid(attributes.sip_password)) {
            delete attributes.sip_password;
        }
        if (_.get(this, 'position.is_doctor', false) === false) {
            delete attributes.doctor;
        }
        
        return attributes;
    }
}

export default EmployeeClinic;