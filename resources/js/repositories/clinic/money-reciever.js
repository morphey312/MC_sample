import BaseRepository from '@/repositories/base-repository';
import MoneyReciever from '@/models/clinic/money-reciever';

class MoneyRecieverRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/clinics/money-recievers';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new MoneyReciever(row);
    }
}

export default MoneyRecieverRepository;