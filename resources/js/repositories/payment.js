import BaseRepository from '@/repositories/base-repository';
import Payment from '@/models/payment';

class PaymentRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/payments';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Payment(row);
    }

    /**
     * Get total
     * 
     * @param {object} filters 
     * 
     * @returns {Promise}
     */
    getTotal(filters) {
        return axios.get(this.buildUrl('get-total', {filters}), true)
            .then((response) => {
                return Promise.resolve(response.data);
            });
    }
    /**
     * Get all payment list
     * @param {*} filters 
     * @param {*} scopes 
     */
    fetchAll(filters = null, sort = null, scopes = null) {
        let url = this.buildUrl('all', {
            ...this.getFilters(filters), 
            ...this.getSort(sort),
            ...this.getScopes(scopes),
        });
        return this.fetchModuleList(url);
    }

    /**
     * Fetch specific list
     * 
     * @param {string} url 
     * 
     * @returns {Promise}
     */
    fetchModuleList(url) {
        let result = this.fetchListInternal(url);
        return result.then((data) => {
            return data;
        });
    }
}

export default PaymentRepository;