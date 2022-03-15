import eventHub from '@/services/event-hub';
import serverTime from '@/services/server-time';
import store from '@/store';
import RelatedAction from '@/models/calls/related-action';
import CONSTANT from '@/constants';
import {Contact, PatientContact, EmployeeContact, ProcessState} from '@/services/sip-ua/process-state';
import {UA} from '@/services/sip-ua';
import {STATE_WRAP_UP, STATE_BUSY, STATE_CONFERENCE} from '@/services/sip-ua/state-manager';
import {makeSafe} from '@/services/safe-close';
import lts from '@/services/lts';
import logger from '@/services/logging';

const getContact = (call) => {
    if (call.patient !== null) {
        return new PatientContact(call.patient);
    }
    if (call.versa !== null) {
        return new Contact(call.versa.id, call.versa.type, call.versa.name, [{
            number: call.phoneNumber,
        }]);
    } 
    return new Contact(null, null, __('Неизвестный'), [{
        number: call.phoneNumber,
    }]);
}

const getEnquiryContact = (enquiry) => {
    if (enquiry.patient !== null) {
        let patientContact = new PatientContact(enquiry.patient);
        if (_.isVoid(patientContact.email)) {
            patientContact.email = enquiry.email;
        }
        return patientContact;
    }
    return new Contact(null, null, enquiry.name, [{
        number: enquiry.phone_number,
    }], enquiry.email);
}

const getIsPatient = (patient) => {
    if (patient !== null && patient.status === CONSTANT.PATIENT.STATUS.PATIENT) {
        return CONSTANT.PROCESS_LOG.PATIENT_TYPE.PATIENT;
    }
    return CONSTANT.PROCESS_LOG.PATIENT_TYPE.NOT_PATIENT;
}

const getIsFirstVisit = (call) => {
    return call.is_first == CONSTANT.CALL.REQUEST_TYPES.REPEATED 
        ? CONSTANT.PROCESS_LOG.VISIT_TYPE.RETURN
        : CONSTANT.PROCESS_LOG.VISIT_TYPE.FIRST;
}

const setContextCall = (state, call) => {
    state.processLog.is_patient = getIsPatient(call.patient);
    state.processLog.clinic = call.clinic === null ? null : call.clinic.id;
    state.processLog.call = call.id;
    state.processLog.source = call.source;
}

const populateContactPool = (state, call) => {
    call.related_contacts.forEach((contact) => {
        if (contact.type === CONSTANT.USER.TYPE.PATIENT) {
            state.addContact(new PatientContact(contact.data));
        } else if (contact.type === CONSTANT.USER.TYPE.EMPLOYEE) {
            state.addContact(new EmployeeContact(contact.data));
        }
    });
}

const startProcessCall = (state, call) => {
    let contact = getContact(call);
    let selected = state.currentContact || contact;
    if (state.processing) {
        setContextCall(state, call);
    } else {
        state.reset();
        setContextCall(state, call);
        state.processLog.sip_number = UA.number;
        state.processLog.started_at = serverTime.asString();
        state.processLog.is_incoming_call = UA.call !== null && UA.call.isIncoming;
        state.startProcess();
    }
    populateContactPool(state, call);
    state.addContact(contact);
    if (state.selectContact(selected.uid) === undefined) {
        state.selectContact(contact.uid);
    }
    state.phoneNumber = call.phoneNumber;        
    store.commit('processState', state);
    logger.log('Call process started', {
        sip: state.processLog.sip_number,
        number: call.phoneNumber,
    });
}

const setContextEnquiry = (state, enquiry) => {
    state.processLog.is_patient = getIsPatient(enquiry.patient);
    state.processLog.clinic = enquiry.clinic_id;
    state.processLog.enquiry = enquiry.id;
    state.processLog.source = 'site_enquiry';
}

const startProcessEnquiry = (state, enquiry) => {
    let contact = getEnquiryContact(enquiry);
    state.reset();
    setContextEnquiry(state, enquiry);
    state.processLog.sip_number = UA.number;
    state.processLog.started_at = serverTime.asString();
    state.upsertContact(contact);
    state.phoneNumber = enquiry.phone_number;
    state.startProcess();
    store.commit('processState', state);
    logger.log('Site enquiry process started', {
        sip: state.processLog.sip_number,
        number: enquiry.phone_number,
    });
}

const startProcessWaitListRecord = (state, record) => {
    let contact = getEnquiryContact(record);
    state.reset();
    setContextWaitListRecord(state, record);
    state.processLog.sip_number = UA.number;
    state.processLog.started_at = serverTime.asString();
    state.upsertContact(contact);
    state.phoneNumber = record.phone_number;
    state.startProcess();
    store.commit('processState', state);
    logger.log('Wait list record process started', {
        sip: state.processLog.sip_number,
        number: record.phone_number,
    });
}

const setContextWaitListRecord = (state, record) => {
    state.processLog.is_patient = getIsPatient(record.patient);
    state.processLog.clinic = record.clinic_id;
    state.processLog.wait_list_record = record.id;
    state.processLog.source = 'wait_list_record';
}

const startProcessUndefined = (state) => {
    state.reset(true);
    state.processLog.source = 'call';
    state.processLog.sip_number = UA.number;
    state.processLog.started_at = serverTime.asString();
    state.startProcess();
    store.commit('processState', state);
}

const addRelatedCall = (state, call) => {
    let contact = getContact(call);
    state.processLog.related_actions.push(new RelatedAction({
        action: CONSTANT.CALL_ACTION.TYPE.CREATE,
        time: serverTime.asString(),
        related_id: call.id,
        related_type: CONSTANT.CALL_ACTION.SUBJECT.CALL_LOG,
    }));
    state.upsertContact(contact);
    state.phoneNumber = call.phoneNumber;
    store.commit('processState', state);
}

makeSafe(store.state.processState, __('Вы не завершили обработку, Вы уверены, что хотите продолжить?'), () => {
    return store.state.processState.processing;
});

eventHub.$on('login', () => {
    if (lts.processState) {
        store.commit('clearProcessState');
    }
});

eventHub.$on('call:initiated', (call) => {
    if (call.isIncoming) {
        let state = store.state.processState;
        let contact = state.findContactByNumber(call.number);
        if (contact === undefined) {
            contact = new Contact(null, null, __('Неизвестный'), [{
                number: call.number,
                comment: '',
            }]);
            state.addContact(contact);
        }
        state.selectContact(contact.uid);
    }
});

eventHub.$on('call:details', (call) => {
    store.commit('callInfo', call);
    let state = store.state.processState;
    if (state.processing && state.hasContext()) {
        addRelatedCall(state, call);
    } else {
        startProcessCall(state, call);
    }
});

eventHub.$on('call:ended', (call) => {
    if (call.missed) {
        let state = store.state.processState;
        state.reset();
        store.commit('processState', state);
    }
});

eventHub.$on('process:enquiry', (enquiry) => {
    let state = store.state.processState;
    if (!state.processing) {
        startProcessEnquiry(state, enquiry);
        UA.stateManager.transit(STATE_WRAP_UP);
    }
});

eventHub.$on('process:wait-list-record', (record) => {
    let state = store.state.processState;
    if (!state.processing) {
        startProcessWaitListRecord(state, record);
        UA.stateManager.transit(STATE_WRAP_UP);
    }
});

eventHub.$on('operator:state-changed', ({from, to}) => {
    let state = store.state.processState;
    if (!state.processing) {
        if ([STATE_BUSY, STATE_CONFERENCE, STATE_WRAP_UP].indexOf(to) !== -1) {
            startProcessUndefined(state);
        }
    }
});

eventHub.$on('processLog:completed', (process) => {
    logger.log('Process log completed', {
        call: process.call,
        enquiry: process.enquiry,
        status: process.status,
        contact_id: process.contact_id,
        contact_type: process.contact_type,
        sip_number: process.sip_number,
    });
});

eventHub.$on('created:Patient', (patient) => {
    let state = store.state.processState;
    if (state.processing) {
        state.processLog.related_actions.push(new RelatedAction({
            action: CONSTANT.CALL_ACTION.TYPE.CREATE,
            time: serverTime.asString(),
            related_id: patient.id,
            related_type: CONSTANT.CALL_ACTION.SUBJECT.PATIENT,
        }));
        state.replaceContact(new PatientContact(patient));
        state.processLog.is_patient = getIsPatient(patient);
        store.commit('processState', state);
    } else if (store.state.user.hasVoIP) {
        state.upsertContact(new PatientContact(patient));
        state.processLog.is_patient = getIsPatient(patient);
        store.commit('processState', state);
    }
});

eventHub.$on('updated:Patient', (patient) => {
    let state = store.state.processState;
    if (state.processing) {
        state.processLog.related_actions.push(new RelatedAction({
            action: CONSTANT.CALL_ACTION.TYPE.UPDATE,
            time: serverTime.asString(),
            related_id: patient.id,
            related_type: CONSTANT.CALL_ACTION.SUBJECT.PATIENT,
        }));
        state.updateContact(new PatientContact(patient));
        store.commit('processState', state);
    } else if (store.state.user.hasVoIP) {
        state.updateContact(new PatientContact(patient));
        state.processLog.is_patient = getIsPatient(patient);
        store.commit('processState', state);
    }
});

eventHub.$on('created:Call', (call) => {
    let state = store.state.processState;
    if (state.processing) {
        state.processLog.related_actions.push(new RelatedAction({
            action: CONSTANT.CALL_ACTION.TYPE.CREATE,
            time: serverTime.asString(),
            related_id: call.id,
            related_type: CONSTANT.CALL_ACTION.SUBJECT.CALL,
        }));
        if (call.is_patient_contact) {
            state.replaceContact(new PatientContact(call.contact));
        } else if (call.is_employee_contact) {
            state.replaceContact(new EmployeeContact(call.contact));
        }
        if (call.is_patient_contact) {
            state.processLog.is_patient = getIsPatient(call.contact);
        } else {
            state.processLog.is_patient = CONSTANT.PROCESS_LOG.PATIENT_TYPE.NOT_PATIENT;
        }
        state.processLog.is_first_visit = getIsFirstVisit(call);
        store.commit('processState', state);
    }
});

eventHub.$on('updated:Call', (call) => {
    let state = store.state.processState;
    if (state.processing) {
        state.processLog.related_actions.push(new RelatedAction({
            action: CONSTANT.CALL_ACTION.TYPE.UPDATE,
            time: serverTime.asString(),
            related_id: call.id,
            related_type: CONSTANT.CALL_ACTION.SUBJECT.CALL,
        }));
        store.commit('processState', state);
    }
});


eventHub.$on('created:Appointment', (appointment) => {
    let state = store.state.processState;
    if (state.processing) {
        state.processLog.related_actions.push(new RelatedAction({
            action: CONSTANT.CALL_ACTION.TYPE.CREATE,
            time: serverTime.asString(),
            related_id: appointment.id,
            related_type: CONSTANT.CALL_ACTION.SUBJECT.APPOINTMENT,
        }));
        store.commit('processState', state);
    }
});

eventHub.$on('updated:Appointment', (appointment) => {
    let state = store.state.processState;
    if (state.processing) {
        state.processLog.related_actions.push(new RelatedAction({
            action: CONSTANT.CALL_ACTION.TYPE.UPDATE,
            time: serverTime.asString(),
            related_id: appointment.id,
            related_type: CONSTANT.CALL_ACTION.SUBJECT.APPOINTMENT,
        }));
        store.commit('processState', state);
    }
});