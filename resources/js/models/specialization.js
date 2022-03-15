import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN
} from '@/services/validation';

class Specialization extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Specialization';
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
            genitive_name: null,
            short_name: null,
            card_template_id: null,
            course_days: 0,
            days_since: null,
            status: 1,
            not_use_for_new_patient_call: false,
            not_show_signal_records: false,
            is_non_profile_patient: false,
            is_non_treatment: false,
            is_check_up: false,
            adjacent_specializations: {},
            service_group: null,
            order: 0,
            online_appointment: false,
            once_in_report: false,
            additional_templates: [],
            is_real_time_appointment: false,
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            adjacent_specializations: (value) => {
                return _.isArray(value) ? {} : value;
            },
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            genitive_name: required.and(maxlen(STRING_MAX_LEN)),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/specializations',
            fetch: '/api/v1/specializations/{id}',
            update: '/api/v1/specializations/{id}',
            delete: '/api/v1/specializations/{id}',
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

export default Specialization;
