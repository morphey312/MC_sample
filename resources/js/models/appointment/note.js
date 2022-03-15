import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    TEXT_MAX_LEN,
} from '@/services/validation';

/**
 * AppointmentNote model
 */
class Note extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            appointment_id: null,
            task: null,
            note: null,
        }
    }

    /**
     * @inheritdoc
     */
    rules() {
        return {
            appointment_id: required,
            task: maxlen(TEXT_MAX_LEN),
            note: maxlen(TEXT_MAX_LEN),
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/appointments/notes',
            fetch: '/api/v1/appointments/notes/{id}',
            update: '/api/v1/appointments/notes/{id}',
        }
    }
}

export default Note;
