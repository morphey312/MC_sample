import BaseRepository from '@/repositories/base-repository';
import SmsReminder from '@/models/sms-reminder';

class SmsReminderRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/sms-reminders';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new SmsReminder(row);
    }
}

export default SmsReminderRepository;
