import BaseModel from '@/models/base-model';

import {
    required,
    attributeEquals,
    gte,
    numeric,
} from '@/services/validation';

class Doctor extends BaseModel {

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            appointment_duration: 10,
            appointment_duration_repeated: 10,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            appointment_duration: required.and(numeric).and(gte(5)),
            appointment_duration_repeated: required.and(numeric).and(gte(5)),
        };
    }
}

export default Doctor;