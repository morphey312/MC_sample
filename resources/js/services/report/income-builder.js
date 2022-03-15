import CONSTANTS from '@/constants';

const WORKSPACE = __('Кабинет');
const PREPAY = __('Аванс');
const ANALYSES = __('АНАЛИЗЫ');
const DIAGNOSTIC = __('ФД');
const ENDOSCOPY = __('ЭНД');
const RENTGEN = __('РЕН');
const UZI = __('УЗИ');
const FIZIO_PAYED = __('ФИЗИО пл');
const MANIPULATION = __('МАН');
const ANESTHESIA = __('Анестезия');
const KT = __('КТ');
const MASSAGE = __('МАСС');
const SUBSIDIARY_SPECIALIZATIONS = [ANALYSES, UZI, FIZIO_PAYED, ENDOSCOPY, DIAGNOSTIC, RENTGEN, MANIPULATION, KT];
const CARD_SPEC_EXCEPTIONS = [ENDOSCOPY, UZI, DIAGNOSTIC, RENTGEN, ANESTHESIA, KT, MASSAGE];

const isSubsidiary = (row) => {
    let specialization = row.appointment_specialization ? __(row.appointment_specialization) : null;
    return row.is_subsidiary &&
        SUBSIDIARY_SPECIALIZATIONS.indexOf(specialization) !== -1 &&
        row.appointment_card_specialization_id != row.appointment_specialization_id;
}

const prepareData = (data, withDoctors = true) => {
    let results = {};
    data.forEach(row => {
        let spec;
        let specialization = row.appointment_specialization ? __(row.appointment_specialization) : null;

        if (row.appointment_specialization_id == row.appointment_card_specialization_id ||
            CARD_SPEC_EXCEPTIONS.indexOf(specialization) !== -1 ||
            isSubsidiary(row)) {
            spec = specialization ? specialization : (row.is_deposit ? PREPAY : __('Мед'));
        } else {
            spec = row.appointment_card_specialization ? __(row.appointment_card_specialization) : null;
        }

        let doctorId = row.doctor_id ? row.doctor_id : (row.is_deposit ? PREPAY : '');

        if (withDoctors == true) {
            if (results[spec] == undefined) {
                results[spec] = {doctors: {}}
                results[spec].doctors[doctorId] = [row];
            } else {
                if (results[spec].doctors[doctorId] == undefined) {
                    results[spec].doctors[doctorId] = [row];
                } else {
                    results[spec].doctors[doctorId].push(row);
                }
            }
        } else {
            if (results[spec] == undefined) {
                results[spec] = [row];
            } else {
                results[spec].push(row);
            }
        }
    });

    return results;
}

const addMissingAppointmentColumns = (results, appointments, withDoctors = true) => {
    appointments.forEach(row => {
        let spec = row.specialization_name ? __(row.specialization_name) : null;
        let isWorkspace = row.doctor_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.WORKSPACE;
        let isWorkspaceHasDoctor = isWorkspace && !_.isNull(row.workspace_doctor_id) ? true : false;
        let doctorId = !isWorkspace ? row.doctor_id : isWorkspaceHasDoctor ? row.workspace_doctor_id : WORKSPACE;
        let doctor_name = !isWorkspace ? row.doctor_name : isWorkspaceHasDoctor ? row.workspace_doctor_name : WORKSPACE;

        if (withDoctors == true) {
            if (!results[spec]) {
                results[spec] = {doctors: {}};
                results[spec].doctors[doctorId] = [{doctor_name}];
            } else {
                if (!results[spec].doctors[doctorId]) {
                    results[spec].doctors[doctorId] = [{doctor_name}];
                }
            }
        } else {
            if (!results[spec]) {
                results[spec] = [];
            }
        }
    });

    return results;
}

const filterAppointments = (appointments, doctorId, specName) => {
    specName = specName ? __(specName) : null;
    return appointments.filter(appointment => {
        let appointmentSpecializationName = appointment.specialization_name ? __(appointment.specialization_name) : null;
        return appointment.doctor_id == doctorId &&
                appointmentSpecializationName == specName &&
                appointment.doctor_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE;
    });
}

const getPatientVisitType = (appointments = [], isFirst = 1) => {
    return appointments.reduce((total, appointment) => {
        if (appointment.is_first == isFirst) {
            total++;
        }
        return total;
    }, 0);
}

const getTreatmentTotal = (appointments = []) => {
    return appointments.reduce((total, appointment) => {
        if (appointment.is_first_in_treatment_course === true) {
            total++;
        }
        return total;
    }, 0);
}

const calcPayed = (payments) => {
    return payments.reduce((total, row) => {
        if (row.type === CONSTANTS.PAYMENT.TYPES.INCOME) {
            total += Number(row.payed_amount);
        }
        if (row.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
            total -= Number(row.payed_amount);
        }
        return total;
    }, 0);
}

export {
    isSubsidiary,
    prepareData,
    addMissingAppointmentColumns,
    CARD_SPEC_EXCEPTIONS,
    filterAppointments,
    getPatientVisitType,
    getTreatmentTotal,
    calcPayed,
}
