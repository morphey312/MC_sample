import BaseRepository from '@/repositories/base-repository';
import Patient from '@/models/patient';
import wait from "@/services/deferred-response";

class PatientRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Patient(row);
    }

    /**
     * Fetch duplicated parients
     *
     * @param {array} compareCriterias
     * @param {object} filters
     * @param {number} page
     * @param {number} limit
     *
     * @returns {Promise}
     */
    fetchDuplicated(compareCriterias, filters = null, scopes = null, page = 1, limit = 50) {
        return axios.get(this.buildUrl('duplicated', {
            compareCriterias,
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
        let cancelToken = this.createCancelToken();
        return axios.post(this.buildUrl('merge'), {
            merge: items,
        }).then((response) => {
            if (response.data.promiseId !== undefined) {
                return wait(response.data.promiseId).then((response) => {
                    return this.createResponse(response, false);
                })
            }
            return this.createResponse(response, false);
        }).finally(() => {
            this.discardCancelToken(cancelToken);
        });
    }
}

export default PatientRepository;
