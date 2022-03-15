import BaseModel from '@/models/base-model';
import {
    required,
    requiredArray,
    maxlen,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
} from '@/services/validation';

/**
 * NotificationTemplate model
 */
class NotificationMailingTemplate extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Notification_Mailing_Template';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            scenario: null,
            channel_id: null,
            mailing_time: null,
            schedule_mailing: false,
            positions: [],
            clinics:[],
            statuses: [],
            specialization_id: null,
            service_id: null,
            disabled: false,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            scenario: required,
            channel_id: required,
        };
    }
    /**
     * @inheritdoc
     */
     mutations() {
        return {
        }
    }
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/notifications/mailing-template',
            fetch: '/api/v1/notifications/mailing-template/{id}',
            update: '/api/v1/notifications/mailing-template/{id}',
            delete: '/api/v1/notifications/mailing-template/{id}',
        };
    }
}

export default NotificationMailingTemplate;
