import CONSTANT from '@/constants';

export default {
    methods: {
        initPatientFromProcessState(patient, state, contact) {
            if (patient.isNew()) {
                patient.contact_details.primary_phone_number = state.phoneNumber;
                if (state.processLog.clinic !== null) {
                    patient.clinics = [state.processLog.clinic];
                    patient.contact_details.primary_phone_clinic = state.processLog.clinic;
                }
                if (state.processLog.enquiry !== null) {
                    patient.full_name = contact.name;
                    patient.contact_details.email = contact.email;
                }
            }
            if (state.processLog.is_patient == CONSTANT.PROCESS_LOG.PATIENT_TYPE.PATIENT) {
                patient.status = CONSTANT.PATIENT.STATUS.PATIENT;
            } else if (state.processLog.is_patient == CONSTANT.PROCESS_LOG.PATIENT_TYPE.NOT_PATIENT) {
                patient.status = CONSTANT.PATIENT.STATUS.GUEST;
            }
            return patient;
        }
    },
};