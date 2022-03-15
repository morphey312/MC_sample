import BaseRepository from '@/repositories/base-repository';
import EmailLog from '@/models/email-log';

class EmailLogRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'created'}],
            ...options,
        });
        this.endpoint = '/api/v1/email-logs';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new EmailLog(row);
    }
}

export default EmailLogRepository;
