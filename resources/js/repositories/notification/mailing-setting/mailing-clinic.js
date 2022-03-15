import BaseRepository from '@/repositories/base-repository';
import NotificationMailingTemplateSettingClinic from "@/models/notification/mailing-setting/mailing-clinic";

class NotificationMailingTemplateSettingClinicRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = 'api/v1/notifications/mailing-templates/mailing-settings/mailing-clinics';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new NotificationMailingTemplateSettingClinic(row);
    }
}

export default NotificationMailingTemplateSettingClinicRepository;
