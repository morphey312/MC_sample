import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * NotificationChannel model
 */
class NotificationMailingProvides extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Notification_Mailing_Providers';
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
            create: '/api/v1/notifications/mailing-provider',
            fetch: '/api/v1/notifications/mailing-provider/{id}',
            update: '/api/v1/notifications/mailing-provider/{id}',
            delete: '/api/v1/notifications/mailing-provider/{id}',
        };
    }
}

export default NotificationMailingProvides;
