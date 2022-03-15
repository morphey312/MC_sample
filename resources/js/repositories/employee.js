import BaseRepository from '@/repositories/base-repository';
import Employee from '@/models/employee';
import EmployeeDaySheet from '@/services/employee-daysheet';

class EmployeeRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'full_name'}],
            ...options,
        });
        this.endpoint = '/api/v1/employees';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Employee(row);
    }

    /**
    * Fetch for resources shedule
    */
    fetchDaySheets(filters) {
        return axios.get(this.buildUrl('fetch_day_sheets', {filters}), true)
            .then((response) => {
                return Promise.resolve(response.data);
            });
    }

    /**
    * Count appointments is_first by period, clinic, specialization
    */
    getIsFirstCount(filters) {
        return axios.get(this.buildUrl(`count_appointment_is_first`, {filters}), true)
            .then((response) => {
                return Promise.resolve(response.data);
            });
    }

    /**
    * Merge employee time sheets
    *
    * @param array data
    *
    * @retuns array
    */
    castEmployeeTimeSheets(data, dateField = "startDate") {
        let service = new EmployeeDaySheet();
        return service.castTimeSheetData(data, dateField);
    }

    /**
     * Fetch list with employee with bonus norm data
     * 
     * @param {object} filters 
     * @param {array} sort 
     * @param {array} scopes
     * @param {number} limit 
     * 
     * @returns {Promise}
     */
    fetchReportList(filters = null, sort = null, scopes = null, limit = null) {
        let url = this.buildUrl('report-list', {
            ...this.getFilters(filters), 
            ...this.getSort(sort),
            ...this.getScopes(scopes),
            limit
        });
        return this.fetchModuleList(url);
    }

    /**
     * Fetch list with employee with specializations
     * 
     * @param {object} filters 
     * @param {array} sort 
     * @param {array} scopes
     * @param {number} limit 
     * 
     * @returns {Promise}
     */
    fetchSurgeryList(filters = null, sort = null, scopes = null, limit = null) {
        let url = this.buildUrl('surgery-list', {
            ...this.getFilters(filters), 
            ...this.getSort(sort),
            ...this.getScopes(scopes),
            limit
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

export default EmployeeRepository;