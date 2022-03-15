import BaseModel from '@/models/base-model';
import moment from 'moment';
import {
    required,
    maxlen,
    assertion,
    TEXT_MAX_LEN,
} from '@/services/validation';

class PersonalTask extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            date: null,
            operator_id: null,
            clinic_id: null,
            specialization_id: null,
            patients: [],
            comment: null,
            attachments: [],
            status: null,
            outcome: null,
            feedback_attachments: [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            operator_id: required.or(assertion(() => this.isFeedback === true)),
            date: required.or(assertion(() => this.isFeedback === true)),
            comment: [
                required.or(assertion(() => this.isFeedback === true)), 
                maxlen(TEXT_MAX_LEN),
            ],
            status: required.or(assertion(() => this.isFeedback !== true)),
            outcome: maxlen(TEXT_MAX_LEN),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/personal-tasks',
            fetch: '/api/v1/personal-tasks/{id}',
            update: '/api/v1/personal-tasks/{id}',
            delete: '/api/v1/personal-tasks/{id}',
        }
    }
    
    /** 
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        let feedbackAttributes = ['status', 'outcome', 'feedback_attachments'];
        let isFeedback = this.isFeedback === true;
        return _.pickBy(attributes, (val, key) => {
            return (feedbackAttributes.indexOf(key) !== -1) === isFeedback;
        });
    }
}

export default PersonalTask;