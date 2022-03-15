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
class NotificationTemplateSettingClinic extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Notification_Template_Setting_Clinic';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            clinic_id: null,
            notification_template_id: null,
            active: true,
            specializations: []
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            clinic_id: required,
            notification_template_id: required,
            active: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/notifications/templates/settings/clinics',
            fetch: '/api/v1/notifications/templates/settings/clinics/{id}',
            update: '/api/v1/notifications/templates/settings/clinics/{id}',
            delete: '/api/v1/notifications/templates/settings/clinics/{id}',
        };
    }
}

export default NotificationTemplateSettingClinic;
