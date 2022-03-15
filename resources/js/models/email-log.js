import BaseModel from '@/models/base-model';

class EmailLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'EmailLog';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            target_type: null,
            target_id: null,
            subject: '',
            from: '',
            to: '',
            event: '',
            event_data: [],
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            fetch: '/api/v1/email-logs/{id}',
        }
    }
}

export default EmailLog;
