import BaseRepository from '@/repositories/base-repository';
import NotificationTemplate from '@/models/notification/template';

class NotificationTemplateRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/notifications/templates';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new NotificationTemplate(row);
    }
}

export default NotificationTemplateRepository;