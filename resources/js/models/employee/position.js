import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
    assertion,
} from '@/services/validation';

class Position extends BaseModel 
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Employee_Position';
    }
    
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            name_lc1: null,
            name_lc2: null,
            name_lc3: null,
            ehealth_position: null,
            ehealth_type: null,
            has_specialization: false,
            is_doctor: false,
            is_operator: false,
            is_cashier: false,
            has_voip: false,
            is_marketing: false,
            is_reception: false,
            is_collector: false,
            is_superviser: false,
            is_surgery: false,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            is_doctor: assertion(() => {
                return  this.is_doctor ? (this.is_operator == false) : true;
            }),
            is_operator: assertion((value) => {
                return  this.is_operator ? (this.is_doctor == false) : true;
            })
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees/positions',
            fetch: '/api/v1/employees/positions/{id}',
            update: '/api/v1/employees/positions/{id}',
            delete: '/api/v1/employees/positions/{id}',
        }
    }

    /**
     * Get localized name
     * 
     * @returns {String}
     */
    get name_i18n() {
        return this.getAttributeI18N('name');
    }
}

export default Position;