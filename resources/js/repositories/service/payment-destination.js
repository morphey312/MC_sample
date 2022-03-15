import BaseRepository from '@/repositories/base-repository';
import PaymentDestination from '@/models/service/payment-destination';

class PaymentDestinationRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/services/payment-destinations';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new PaymentDestination(row);
    }
}

export default PaymentDestinationRepository;