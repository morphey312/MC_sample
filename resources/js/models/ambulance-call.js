import BaseModel from "@/models/base-model";

import {
    required,
    maxlen,
    phoneNumber,
    missing,
    STRING_MAX_LEN
} from "@/services/validation";

class AmbulanceCall extends BaseModel {
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            appointment_id: null,
            caller: '',
            phone: '',
            street: '',
            house: '',
            porch: '',
            flat: '',
            storey: '',
            reason: '',
            comment: '',
            patient_secondary_phone: false,
            is_patient: false,
            patient_home_address: false,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            reason: required,
            caller: required,
            phone: required,
            street: required,
            house: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: "/api/v1/appointments/ambulance-calls",
            fetch: "/api/v1/appointments/ambulance-calls/{id}",
            update: "/api/v1/appointments/ambulance-calls/{id}",
            delete: "/api/v1/appointments/ambulance-calls/{id}"
        };
    }
}

export default AmbulanceCall;
