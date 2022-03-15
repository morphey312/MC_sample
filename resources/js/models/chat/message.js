import BaseModel from '@/models/base-model';

class Message extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            employee_id: null,
            room_id: null,
            text: null,
            created_at: null,
            click_action: false,
            notificationMessage: null,

        }
    }
    /** 
     * @inheritdoc
     */
     routes() {
        return {
            create: '/api/v1/chat/message',
        }
    }
}

export default Message;
