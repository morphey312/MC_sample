import BaseRepository from '@/repositories/base-repository';
import PaymentMethod from '@/models/payment-method';

class PaymentMethodRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/payment-methods';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new PaymentMethod(row);
    }
}

export default PaymentMethodRepository;