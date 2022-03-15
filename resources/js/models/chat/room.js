import BaseModel from '@/models/base-model';

class Room extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            employees: [],
            messages: [],
        }
    }

     /** 
     * @inheritdoc
     */
      routes() {
        return {
            fetch: '/api/v1/chat/room/{id}',
            update: '/api/v1/chat/room/{id}',
        }
    }
}

export default Room;
