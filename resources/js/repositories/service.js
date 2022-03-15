import BaseRepository from '@/repositories/base-repository';
import Service from '@/models/service';
import CONSTANTS from '@/constants';

class ServiceRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'name', direction: 'asc'}],
            ...options
        });
        this.endpoint = '/api/v1/services';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Service(row);
    }

    /**
     * Fetch list with prices
     *
     * @param {object} filters
     *
     * @returns {Promise}
     */
    fetchListForAppointment(filters = {}, params = {}) {
        return axios.get(this.buildUrl('appointment_list', {filters, ...params}))
            .then((response) => {
                return response.data;
            });
    }

    /**
     * Fetch price list
     *
     * @param {object} filters
     * @param {array} sort
     * @param {array} scopes
     * @param {number} page
     *
     * @returns {Promise}
     */
    fetchPriceList(filters = null, sort = null,scopes= null, page = 1, limit = 50) {
        return this.fetchInternal(this.buildUrl('price-list', {
            ...this.getFilters(filters),
            ...this.getSort(sort),
            page,
            limit,
        }));
    }

    /**
     * Fetch duplicated services
     *
     * @param {object} filters
     * @param {number} page
     * @param {number} limit
     *
     * @returns {Promise}
     */
    fetchDuplicated(filters = null, scopes = null, page = 1, limit = 50) {
        return axios.get(this.buildUrl('duplicated', {
            ...this.getFilters(filters),
            ...this.getScopes(scopes),
            page,
            limit,
        })).then((response) => {
            return response.data.map((row) => {
                return row.map(item => this.transformRow(item));
            });
        });
    }

    /**
     * Merge items
     *
     * @param {object} items
     *
     * @returns {Promise}
     */
    merge(items) {
        return axios.post(this.buildUrl('merge'), {
            merge: items,
        }).then((response) => {
            return response.data;
        });
    }
}

export default ServiceRepository;
