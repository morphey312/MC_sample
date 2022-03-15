import BaseRepository from '@/repositories/base-repository';
import AppointmentService from '@/models/appointment/service';
import CONSTANTS from '@/constants';

class ServiceRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/appointments/services';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new AppointmentService(row);
    }

    /**
     * Fetch debts
     *
     * @param {object} filters
     * @param {array} sort
     * @param {array} scopes
     * @param {number} page
     *
     * @returns {Promise}
     */
    fetchDebts(filters = null, sort = null, scopes = null, page = 1, limit = 50) {
        return axios.get(this.buildUrl('debts', {
                ...this.getFilters(filters),
                ...this.getSort(sort),
                ...this.getScopes(scopes),
                page,
                limit,
            })).then((response) => {
                return Promise.resolve(response.data);
            });
    }

    /**
     * Fetch not debts
     *
     * @param {object} filters
     * @param {array} sort
     * @param {array} scopes
     * @param {number} page
     *
     * @returns {Promise}
     */
    fetchNotDebts(filters = null, sort = null, scopes = null, page = 1, limit = 50) {
        return axios.get(this.buildUrl('not-debts', {
            ...this.getFilters(filters),
            ...this.getSort(sort),
            ...this.getScopes(scopes),
            page,
            limit,
        })).then((response) => {
            return Promise.resolve(response.data);
        });
    }

    /**
     * Fetch patient payable services
     *
     * @param {object} filters
     * @param {array} sort
     * @param {array} scopes
     * @param {number} page
     *
     * @returns {Promise}
     */
    fetchPayeableServices(filters = null, sort = null, scopes = null, page = 1, limit = 50) {
        return axios.get(this.buildUrl('payable-services', {
                ...this.getFilters(filters),
                ...this.getSort(sort),
                ...this.getScopes(scopes),
                page,
                limit,
            })).then((response) => {
                return Promise.resolve(response.data);
            });
    }

    /**
     * Fetch debts
     *
     * @param {object} filters
     *
     * @returns {Promise}
     */
    fetchPayments(filters = {}) {
        return axios.get(this.buildUrl('payments', {filters})).then((response) => {
                return Promise.resolve(response.data);
            });
    }

    /**
     * Save services attributes
     *
     * @param {array} services
     *
     * @returns {Promise}
     */
    saveServicesAttributes(services) {
        return axios.post(this.buildUrl('save-attributes'), {services}).then((response) => {
            return Promise.resolve(response.data);
        });
    }

    /**
     * Get services with insurance policy
     *
     * @param {object} filters
     * @param {array} sort
     * @param {array} scopes
     * @param {number} page
     * @param {number} limit
     *
     * @returns {Promise}
     */
    fetchInsuranceExportList(filters, sort = null, scopes = null, page = 1, limit = 50) {
        return this.fetchInternal(this.buildUrl('get-execute-act-list', {
            ...this.getFilters(filters),
            ...this.getSort(sort),
            ...this.getScopes(scopes),
            page,
            limit,
        })).then(data => {
            return Promise.resolve({
                pagination: data.pagination,
                rows: this.prepareInsuranceData(data.rows)
            })
        });
    }

    /**
     * Get services with insurance policy
     *
     * @param {object} filters
     * @param {array} sort
     *
     * @returns {Promise}
     */
    fetchInsuranceActList(filters, sort = null) {
        return this.fetchInternal(this.buildUrl('get-insurance-act-list', {
            ...this.getFilters(filters),
            ...this.getSort(sort),
        }));
    }

    /**
     * Prepare rows with insurance data
     * @param {array} rows
     */
    prepareInsuranceData(rows) {
        return rows.map(row => {
            if (row.container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.MEDICINES && _.isFilled(row.container_data)) {
                row.appointment_date = row.appointment_date || row.container_data.date;
                row.policy_number = row.policy_number || row.container_data.policy_number;
                row.patient_card = row.patient_card || row.container_data.patient_card;
                row.diagnosis = row.diagnosis || row.container_data.diagnosis;
                row.patient_name = row.patient_name || row.container_data.patient_name;
            }
            return row;
        });
    }
}

export default ServiceRepository;
