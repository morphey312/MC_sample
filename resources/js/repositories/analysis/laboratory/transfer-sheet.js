import BaseRepository from '@/repositories/base-repository';
import TransferSheet from '@/models/analysis/laboratory/transfer-sheet';

class TransferSheetRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'created_at', direction: 'desc'}],
            ...options
        });
        this.endpoint = '/api/v1/analysis/laboratories/transfer-sheets';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new TransferSheet(row);
    }
}

export default TransferSheetRepository;
