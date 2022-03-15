import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * NotificationChannel model
 */
class NotificationChannel extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Notification_Channel';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
            type: null,
            account_name: null,
            account_password: null,
            settings: {},
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            type: required,
            account_name: maxlen(STRING_MAX_LEN),
            account_password: maxlen(STRING_MAX_LEN),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/notifications/channels',
            fetch: '/api/v1/notifications/channels/{id}',
            update: '/api/v1/notifications/channels/{id}',
            delete: '/api/v1/notifications/channels/{id}',
        };
    }
}

export default NotificationChannel;
