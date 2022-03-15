import BaseRepository from '@/repositories/base-repository';
import DaySheet from '@/models/day-sheet';
import EmployeeDaySheet from '@/services/employee-daysheet';
import Pagination from '@/repositories/pagination';
import { dateFormat } from '@/services/format';
const qs = require('qs');

class DaySheetRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/day-sheets';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new DaySheet(row);
    }

    /**
    * Fetch period schedule
    * 
    * @param {object} filters
    *
    * @returns {Promise}
    */
    fetchUserSchedule(filters = {}) {
        filters.dateStart = dateFormat(filters.dateStart, "YYYY-MM-DD");
        filters.dateEnd = dateFormat(filters.dateEnd, "YYYY-MM-DD");

        return this.fetchListInternal(this.buildUrl('all', {filters}), false)
                   .then((response) => {    
                        if (filters.employees) {
                            return Promise.resolve(this.castEmployeeTimeSheets(response));
                        }

                        return Promise.resolve(response.map((row) => this.transformRow(row)));
                   });
    }

    /**
    * Merge employee time sheets
    *
    * @param {array} data
    *
    * @retun {array}
    */
    castEmployeeTimeSheets(data) {
        let service = new EmployeeDaySheet();
        return service.castTimeSheetData(data);
    }

    /**
     * Fetch data
     *
     * @param {object} filters
     * @param {array} sort
     *
     * @returns {Promise}
     */
    getSchedules(filters = {}, sort = []) {
        let scopes = ['default', 'locks', 'owner', 'appointments', 'limitations'];
        return axios.post(this.buildUrl('appointmentSchedule'), {filters, sort, scopes})
            .then((response) => {
                return response.data;
            });
    }

    /**
    * Fetch employee single day with appointments
    * 
    * @param {object} filters
    * @param {bool} compact
    *
    * @returns {Promise}
    */
    fetchSingleDay(filters = {}, compact = false) {
        return axios.get(this.buildUrl('singleDaySheet', {
            filters, 
            compact: compact ? 1 : 0,
            scopes: ['default', 'locks', 'owner', 'appointments', 'limitations','clinic'],
        })).then((response) => {
            return response.data;
        });
    }

    /**
    * Fetch report list
    * 
    * @param {object} filters
    * @param {array} sort
    *
    * @returns {Promise}
    */
    fetchReportList(filters = {}, sort = []) {
        return axios.get(this.buildUrl('get-report-list', {
            filters, 
            sort,
        })).then((response) => {
            return response.data;
        });
    }

    /**
     * Count day sheets by clinic
     * 
     * @param {*} filters 
     * 
     * @returns {Promise}
     */
    fetchCount(filters = {}) {
        return axios.get(this.buildUrl('get-count', {
            filters, 
        })).then((response) => {
            return response.data;
        });
    }
}

export default DaySheetRepository;