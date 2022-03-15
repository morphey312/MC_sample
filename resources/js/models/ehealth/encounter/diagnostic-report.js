import BaseModel from '@/models/base-model';
import {
    required,
    date,
    boolean,
    maxlen,
    assertion,
    ukrSpelling,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
    integer,
} from '@/services/validation';

class DiagnosticReport extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            encounter_id: null,
            issued: null,
            category: null,
            recorded_by: null,
            conclusion: null,
            division: null,
            conclusion_code: null,
            code: null,
            effective_period_start: null,
            effective_period_end: null,
            results_interpreter: null,
            performer: null,
            paper_referral: false,
            paper_referral_requester_employee_name: null,
            paper_referral_requisition: null,
            paper_referral_service_request_date: null,
            paper_referral_note: null,
            paper_referral_requester_legal_entity_name: null,
            paper_referral_requester_legal_entity_edrpou: null,
            primary_source: false,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            encounter_id: required,
            code: required.and(integer),
            issued: required.and(date),
            category: required.and(maxlen(STRING_MAX_LEN)),
            conclusion: maxlen(TEXT_MAX_LEN).and(ukrSpelling),
            effective_period_start: required.and(date),
            recorded_by: required.and(integer),
            paper_referral: boolean,
            paper_referral_note: maxlen(TEXT_MAX_LEN).and(ukrSpelling),
            paper_referral_requisition: ukrSpelling.and(maxlen(STRING_MAX_LEN)),
            paper_referral_requester_legal_entity_name: ukrSpelling.and(maxlen(STRING_MAX_LEN)),
            paper_referral_requester_legal_entity_edrpou: required.or(assertion(() => {
                return !(this.paper_referral === true)
            }).and(maxlen(STRING_MAX_LEN).and(ukrSpelling))),
            paper_referral_requester_employee_name: required.or(assertion(() => {
                return !(this.paper_referral === true)
            }).and(maxlen(STRING_MAX_LEN).and(ukrSpelling))),
            paper_referral_service_request_date: required.or(assertion(() => {
                return !(this.paper_referral === true)
            })),
            primary_source: boolean,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/ehealth/encounter/diagnostic-reports',
            fetch: '/api/v1/ehealth/encounter/diagnostic-reports/{id}',
            update: '/api/v1/ehealth/encounter/diagnostic-reports/{id}',
            delete: '/api/v1/ehealth/encounter/diagnostic-reports/{id}',
        }
    }
}

export default DiagnosticReport;
