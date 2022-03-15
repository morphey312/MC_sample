import BaseRepository from '@/repositories/base-repository';
import NotificationTemplate from '@/models/notification/template';
import NotificationMailingProvider from "../../models/notification/mailing-provider";
import NotificationMailingTemplate from "../../models/notification/mailing-template";

class NotificationMailingTemplateRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/notifications/mailing-template';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new NotificationMailingTemplate(row);
    }
}

export default NotificationMailingTemplateRepository;
