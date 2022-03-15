export default {
    methods: {
        extendPatientData(registration, patient) {
            if (_.isVoid(patient.firstname) && _.isFilled(registration.firstname)) {
                patient.firstname = registration.firstname;
            }
            if (_.isVoid(patient.lastname) && _.isFilled(registration.lastname)) {
                patient.lastname = registration.lastname;
            }
            if (_.isVoid(patient.middlename) && _.isFilled(registration.middlename)) {
                patient.middlename = registration.middlename;
            }
            if (_.isVoid(patient.birthday) && _.isFilled(registration.birthday)) {
                patient.birthday = registration.birthday;
            }
            if (_.isVoid(patient.contact_details.email) && _.isFilled(registration.email)) {
                patient.contact_details.email = registration.email;
            }
            if (patient.contact_details.secondary_phone_number === registration.phone) {
                patient.contact_details.secondary_phone_number = patient.contact_details.primary_phone_number;
                patient.contact_details.primary_phone_number = registration.phone;
            } else {
                if (patient.contact_details.primary_phone_number !== registration.phone) {
                    if (_.isFilled(patient.contact_details.secondary_phone_number)) {
                        let comment = _.isFilled(patient.comment) ? (patient.comment + '\n') : '';
                        patient.comment = comment + __('Доп. телефон: {phone}', {phone: patient.contact_details.secondary_phone_number});
                    }
                    patient.contact_details.secondary_phone_number = patient.contact_details.primary_phone_number;
                    patient.contact_details.primary_phone_number = registration.phone;
                }
            }
            patient.registration_id = registration.id;
            patient.has_registration = true;
        },
    },
};
