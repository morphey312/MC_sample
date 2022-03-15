import BaseRepository from '@/repositories/base-repository';
import NotificationChannel from '@/models/notification/channel';

class NotificationChannelRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/notifications/channels';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new NotificationChannel(row);
    }
}

export default NotificationChannelRepository;