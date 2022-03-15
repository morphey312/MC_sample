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
class NotificationTemplate extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Notification_Template';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            subject: null,
            scenario: null,
            channel_id: null,
            parent_id: null,
            time: null,
            header: null,
            inherit_header: false,
            body: null,
            inherit_body: false,
            footer: null,
            positions: [],
            specialization_id: null,
            service_id: null,
            inherit_footer: false,
            disabled: false,
            clinics: [],
            voip_queue: [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            subject: maxlen(STRING_MAX_LEN),
            scenario: required,
            channel_id: required,
            header: maxlen(TEXT_MAX_LEN),
            body: maxlen(TEXT_MAX_LEN),
            footer: maxlen(TEXT_MAX_LEN),
        };
    }
    /**
     * @inheritdoc
     */
     mutations() {
        return {
            voip_queue: (value) => {
                return value.map(item => {
                    return {
                        id: item.id? item.id : '',
                        queue: item.queue? item.queue : item,
                    }
                });
            }
        }
    }
    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/notifications/templates',
            fetch: '/api/v1/notifications/templates/{id}',
            update: '/api/v1/notifications/templates/{id}',
            delete: '/api/v1/notifications/templates/{id}',
        };
    }
}

export default NotificationTemplate;
