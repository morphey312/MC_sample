import BaseModel from '@/models/base-model';
import {
    required,
    assertion,
    maxlen,
    date,
    integer,
    requiredArray,
    ukrSpelling,
    STRING_MAX_LEN,
    boolean,
    TEXT_MAX_LEN,
} from '@/services/validation';
import CONSTANTS from '@/constants';

class Encounter extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            care_episode_id: null,
            appointment_id: null,
            type: null,
            reasons: [],
            priority: null,
            prescriptions: null,
            hospitalization: false,
            hospitalization_admit_source: null,
            hospitalization_destination: null,
            hospitalization_discharge_disposition: null,
            hospitalization_re_admission: null,
            hospitalization_pre_admission_identifier: null,
            incoming_referral: null,
            paper_referral: 'none',
            paper_referral_requester_employee_name: null,
            paper_referral_requisition: null,
            paper_referral_service_request_date: null,
            paper_referral_note: null,
            paper_referral_requester_legal_entity_name: null,
            paper_referral_requester_legal_entity_edrpou: null,
            explanatory_letter: null,
            cancellation_reason: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            reasons: requiredArray,
            prescriptions: maxlen(TEXT_MAX_LEN).and(ukrSpelling),
            type: required,
            appointment_id: required.and(integer),
            care_episode_id: required.and(integer),
            hospitalization: boolean,
            paper_referral_note: maxlen(TEXT_MAX_LEN).and(ukrSpelling),
            paper_referral_requisition: ukrSpelling.and(maxlen(STRING_MAX_LEN)),
            paper_referral_requester_legal_entity_name: ukrSpelling.and(maxlen(STRING_MAX_LEN)),
            date: required.and(date),
            incoming_referral: required.or(assertion(() => {
                return !(this.paper_referral === CONSTANTS.ENCOUNTERS.REFERRAL_TYPE.INCOMING)
            })),
            paper_referral_requester_legal_entity_edrpou: required.or(assertion(() => {
                return !(this.paper_referral === CONSTANTS.ENCOUNTERS.REFERRAL_TYPE.PAPER)
            }).and(maxlen(STRING_MAX_LEN).and(ukrSpelling))),
            paper_referral_requester_employee_name: required.or(assertion(() => {
                return !(this.paper_referral === CONSTANTS.ENCOUNTERS.REFERRAL_TYPE.PAPER)
            }).and(maxlen(STRING_MAX_LEN).and(ukrSpelling))),
            paper_referral_service_request_date: required.or(assertion(() => {
                return !(this.paper_referral === CONSTANTS.ENCOUNTERS.REFERRAL_TYPE.PAPER)
            })),
            cancellation_reason: required.or(assertion(() => !this.isClose)),
            explanatory_letter: maxlen(TEXT_MAX_LEN).and(ukrSpelling),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/ehealth/encounters',
            fetch: '/api/v1/ehealth/encounters/{id}',
            update: '/api/v1/ehealth/encounters/{id}',
            delete: '/api/v1/ehealth/encounters/{id}',
        }
    }
}

export default Encounter;
