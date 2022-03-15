import BaseRepository from '@/repositories/base-repository';
import NotificationTemplateSettingClinic from '@/models/notification/settings/clinic';

class NotificationTemplateSettingClinicRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = 'api/v1/notifications/templates/settings/clinics';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new NotificationTemplateSettingClinic(row);
    }
}

export default NotificationTemplateSettingClinicRepository;
