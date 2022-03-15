import BaseRepository from '@/repositories/base-repository';
import Appointment from '@/models/appointment';

class AppointmentRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = options.endpoint || '/api/v1/appointments';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Appointment(row);
    }

    /**
     * Fetch surgery list
     *
     * @param {object} filters
     * @param {array} sort
     * @param {array} scopes
     * @param {number} page
     * @param {number} limit
     *
     * @returns {Promise}
     */
    fetchSurgeryList(filters = null, sort = null, scopes, page = 1, limit = 50) {
        return this.fetchInternal(this.buildUrl('surgery-list', {
            ...this.getFilters(filters),
            ...this.getSort(sort),
            ...this.getScopes(scopes),
            page,
            limit,
        }));
    }
}

export default AppointmentRepository;
