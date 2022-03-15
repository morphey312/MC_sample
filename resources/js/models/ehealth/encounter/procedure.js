import BaseModel from '@/models/base-model';
import {
    required,
    date,
    boolean,
    integer,
    maxlen,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
    ukrSpelling,
    assertion,
} from '@/services/validation';

class Procedure extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            encounter_id: null,
            code: null,
            category: null,
            division: null,
            recorded_by: null,
            performer: null,
            outcome: null,
            note: null,
            performed_date_time: null,
            paper_referral: false,
            paper_referral_requester_employee_name: null,
            paper_referral_requisition: null,
            paper_referral_service_request_date: null,
            paper_referral_note: null,
            paper_referral_requester_legal_entity_name: null,
            paper_referral_requester_legal_entity_edrpou: null,
            primary_source: false,
            explanatory_letter: null,
            status_reason: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            encounter_id: required,
            code: required.and(integer),
            category: required.and(maxlen(STRING_MAX_LEN)),
            recorded_by: required.and(integer),
            outcome: maxlen(STRING_MAX_LEN),
            note: maxlen(TEXT_MAX_LEN),
            performed_date_time: required.and(date),
            paper_referral: boolean,
            paper_referral_note: maxlen(TEXT_MAX_LEN).and(ukrSpelling),
            paper_referral_requisition: ukrSpelling.and(maxlen(STRING_MAX_LEN)),
            paper_referral_requester_legal_entity_name: ukrSpelling.and(maxlen(STRING_MAX_LEN)),
            paper_referral_service_request_date: required.or(assertion(() => {
                return !(this.paper_referral === true)
            })),
            paper_referral_requester_legal_entity_edrpou: required.or(assertion(() => {
                return !(this.paper_referral === true)
            }).and(maxlen(STRING_MAX_LEN).and(ukrSpelling))),
            paper_referral_requester_employee_name: required.or(assertion(() => {
                return !(this.paper_referral === true)
            }).and(maxlen(STRING_MAX_LEN).and(ukrSpelling))),
            primary_source: boolean,
            explanatory_letter: maxlen(TEXT_MAX_LEN).and(ukrSpelling),
            status_reason: required.or(assertion(() => !this.isCancel)),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/ehealth/encounter/procedures',
            fetch: '/api/v1/ehealth/encounter/procedures/{id}',
            update: '/api/v1/ehealth/encounter/procedures/{id}',
            delete: '/api/v1/ehealth/encounter/procedures/{id}',
        }
    }
}

export default Procedure;
