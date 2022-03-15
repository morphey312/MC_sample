import BaseRepository from '@/repositories/base-repository';
import NotificationMailingProvider from "@/models/notification/mailing-provider";

class NotificationMailingProviderRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/notifications/mailing-provider';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new NotificationMailingProvider(row);
    }
}

export default NotificationMailingProviderRepository;
