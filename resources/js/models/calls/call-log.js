import BaseModel from '@/models/base-model';
import Patient from '@/models/patient';

/**
 * Call log model
 */
class CallLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            fetch: '/api/v1/calls/call-logs/{id}',
        }
    }
}

export default CallLog;