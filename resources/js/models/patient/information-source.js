import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    requiredArray,
    STRING_MAX_LEN
} from '@/services/validation';
import CONSTANT from '@/constants';

class InformationSource extends BaseModel 
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Patient_InformationSource';
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
            is_active: 1,
            employee_id: null,
            is_collective_form: false,
            show_in_appointment: false,
            media_type: null,
            clinics: [],
        };
    }
    
    /** 
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            name_lc1: maxlen(STRING_MAX_LEN),
            name_lc2: maxlen(STRING_MAX_LEN),
            name_lc3: maxlen(STRING_MAX_LEN),
            is_active: required,
            media_type: required,
            clinics: requiredArray,
        };
    }
    
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/information-sources',
            fetch: '/api/v1/patients/information-sources/{id}',
            update: '/api/v1/patients/information-sources/{id}',
            delete: '/api/v1/patients/information-sources/{id}',
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

export default InformationSource;