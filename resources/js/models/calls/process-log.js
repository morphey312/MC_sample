import BaseModel from '@/models/base-model';
import RelatedAction from './related-action';
import {
    required,
    assertion,
} from '@/services/validation';
import CONSTANTS from '@/constants';
import handbook from '@/services/handbook';

/**
 * Process log model
 */
class ProcessLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            call: null,
            enquiry: null,
            wait_list_record: null,
            status: null,
            unprocessibility_reason: null,
            unprocessibility_reason_comment: null,
            comment: null,
            is_patient: null,
            is_first_visit: null,
            source: null,
            clinic: null,
            contact_id: null,
            contact_type: null,
            is_incoming_call: false,
            sip_number: null,
            phone_number: null,
            started_at: null,
            related_actions: [],
        };
    }
    
    /**
     * @inheritdoc
     */
    mutations() {
        return {
            related_actions: (value) => _.isArray(value) ? value.map((v) => this.castToInstance(RelatedAction, v)) : [],
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            status: required,
            unprocessibility_reason: required.or(assertion(() => {
                return this.status !== CONSTANTS.PROCESS_LOG.STATUS.IMPROCESSIBLE;
            })),
            unprocessibility_reason_comment: required.or(assertion(() => {
                return this.status !== CONSTANTS.PROCESS_LOG.STATUS.IMPROCESSIBLE 
                    || this.unprocessibility_reason !== CONSTANTS.PROCESS_LOG.IMPROCESSIBLE_REASON_OTHER;
            })),
            comment: required.or(assertion(() => {
                return this.status !== CONSTANTS.PROCESS_LOG.STATUS.NONPROCESSED;
            })),
            is_patient: required.or(assertion(() => {
                return this.status !== CONSTANTS.PROCESS_LOG.STATUS.PROCESSED;
            })),
            is_first_visit: required.or(assertion(() => {
                return this.status !== CONSTANTS.PROCESS_LOG.STATUS.PROCESSED;
            })),
            contact_id: required.or(assertion(() => {
                return this.status !== CONSTANTS.PROCESS_LOG.STATUS.PROCESSED;
            })),
            related_actions: (value) => {
                if (this.status === CONSTANTS.PROCESS_LOG.STATUS.PROCESSED) {
                    let hasActions = value.some((action) => {
                        return action.related_type == CONSTANTS.CALL_ACTION.SUBJECT.CALL
                            || action.related_type == CONSTANTS.CALL_ACTION.SUBJECT.APPOINTMENT;
                    });
                    return Promise.resolve(hasActions ? false : {related_actions: 'Actions are required'});
                }
                return Promise.resolve(false);
            },
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/calls/process-logs',
            fetch: '/api/v1/calls/process-logs/{id}',
            update: '/api/v1/calls/process-logs/{id}',
        }
    }
    
    /**
     * Get unprocessibility status comment
     * 
     * @return {string}
     */ 
    get status_comment() {
        if (this.status === CONSTANTS.PROCESS_LOG.STATUS.IMPROCESSIBLE) {
            return String(this.unprocessibility_reason) === CONSTANTS.PROCESS_LOG.IMPROCESSIBLE_REASON_OTHER
                ? this.unprocessibility_reason_comment
                : handbook.getOption('reason_impossibility_of_call_processing', this.unprocessibility_reason);
        } else if (this.status === CONSTANTS.PROCESS_LOG.STATUS.NONPROCESSED) {
            return this.comment;
        }
        return null;
    }
    
    /**
     * Serialize data
     * 
     * @returns {object}
     */ 
    serialize() {
        return {
            id: this.id,
            call: this.call,
            enquiry: this.enquiry,
            wait_list_record: this.wait_list_record,
            status: this.status,
            unprocessibility_reason: this.unprocessibility_reason,
            unprocessibility_reason_comment: this.unprocessibility_reason_comment,
            is_patient: this.is_patient,
            is_first_visit: this.is_first_visit,
            source: this.source,
            clinic: this.clinic,
            contact_id: this.contact_id,
            contact_type: this.contact_type,
            is_incoming_call: this.is_incoming_call,
            sip_number: this.sip_number,
            started_at: this.started_at,
            related_actions: this.related_actions.map((item) => {
                return {
                    action: item.action,
                    time: item.time,
                    related_id: item.related_id,
                    related_type: item.related_type,
                };
            }),
        };
    }
}

export default ProcessLog;