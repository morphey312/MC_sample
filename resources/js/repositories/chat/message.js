import BaseRepository from '@/repositories/base-repository';
import Message from '@/models/chat/message';
import CONSTANTS from '@/constants';

class MessageRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'created_at', direction: 'desc'}],
            ...options,
        });
        this.endpoint = '/api/v1/chat/message';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Message(row);
    }
}

export default MessageRepository;
