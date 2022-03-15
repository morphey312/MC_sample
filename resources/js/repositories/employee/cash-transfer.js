import BaseRepository from '@/repositories/base-repository';
import CashTransferModel from '@/models/employee/cashbox/cash-transfer';

class CashTransfer extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/employees/cashbox/cash-transfers';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new CashTransferModel(row);
    }
}

export default CashTransfer;
