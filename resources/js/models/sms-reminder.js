import BaseModel from '@/models/base-model';

/**
 * Call log model
 */
class SmsReminder extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            scheduled_at: null,
            appointment_id: null,
            template_id: null,
            status: '',
            vendor_id: null,
            vendor_data: null,
            type: null,

        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/sms-reminders',
            fetch: '/api/v1/sms-reminders/{id}',
        }
    }
}

export default SmsReminder;
