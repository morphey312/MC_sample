import BaseRepository from '@/repositories/base-repository';
import Notification from '@/models/notification';

class NotificationRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'desc', field: 'created_at'}],
            ...options,
        });
        this.endpoint = '/api/v1/notification';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Notification(row);
    }

}

export default NotificationRepository;
