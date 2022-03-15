import BaseRepository from '@/repositories/base-repository';
import MoneyRecieverCashbox from "@/models/money-reciever/cashbox";

class MoneyRecieverCashboxRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/clinics/money-recievers/cashboxes';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new MoneyRecieverCashbox(row);
    }
}

export default MoneyRecieverCashboxRepository;
