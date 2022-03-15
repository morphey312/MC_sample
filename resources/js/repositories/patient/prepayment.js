import BaseRepository from '@/repositories/base-repository';
import Prepayment from '@/models/patient/prepayment';

class PrepaymentRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/prepayments';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Prepayment(row);
    }

    /**
     * Save prepayments attributes
     * 
     * @param {array} prepayments 
     * 
     * @returns {Promise}
     */
    saveAttributes(prepayments) {
        return axios.post(this.buildUrl('save-attributes'), {prepayments})
            .then(() => {
                return Promise.resolve();
            });
    }
}

export default PrepaymentRepository;