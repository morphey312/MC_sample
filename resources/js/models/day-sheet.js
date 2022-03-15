import BaseModel from '@/models/base-model';
import TimeSheet from '@/models/day-sheet/time-sheet';
import {dateFormat} from '@/services/format';
import {
    integer,
    required,
    requiredArray,
} from '@/services/validation';

class DaySheet extends BaseModel
{
    /**
     * @inheritdoc
     */
    constructor(attributes = {}, collection = null, options = {}) {
        super(attributes, collection, options);
        this._isOutOfLimit = false;
    }
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            day_sheet_owner_id: null,
            day_sheet_owner_type: '',
            clinic_id: null,
            doctor_id: null,
            dates: [],
            time_sheets: [],
            doctor: null,
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            time_sheets: (value) => {
                return _.map(value, (item) => {
                    return this.castToInstance(TimeSheet, item);
                })
            },
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            employee_id: required,
            clinic_id: required,
            dates: requiredArray,
            time_sheets: (items) => this.validateModelsArray(items)
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/day-sheets',
            fetch: '/api/v1/day-sheets/{id}',
            update: '/api/v1/day-sheets/{id}',
            delete: '/api/v1/day-sheets/{id}',
            lock: '/api/v1/day-sheets/lock/{id}',
            unlock: '/api/v1/day-sheets/unlock/{id}',
            listUnlock: '/api/v1/day-sheets/unlock-list/{id}',
            related: '/api/v1/day-sheets/related/{id}',
            deleteList: '/api/v1/day-sheets/delete-list',
        }
    }

    /**
     * @inheritdoc
     */
    options() {
        return {
            methods: {
                lock: 'POST',
                unlock: 'POST',
                related: 'GET',
            }
        }
    }

    /**
     * Send new lock
     *
     * @param array list
     * @param object newLock
     *
     * @return Promise
     */
    lock(list = array(), newLock = {}) {
        let method = this.getOption('methods.lock');
        let route  = this.getRoute('lock');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data   = {
            id: this.id,
            list: list,
            item: newLock,
        };

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        }).catch((e) => {
            return Promise.reject(e);
        });
    }

    /**
    * Send unlock
    *
    * @param object newLock
    *
    * @return Promise
    */
    unlock(item) {
        let method = this.getOption('methods.unlock');
        let route  = this.getRoute('unlock');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data   = {
            id: this.id,
            item: item,
        };

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
    * Send unlock
    *
    * @param object newLock
    *
    * @return Promise
    */
    listUnlock(ids) {
        let method = this.getOption('methods.unlock');
        let route  = this.getRoute('listUnlock');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data   = {
            id: this.id,
            list: ids,
        };

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Add new time sheet row
     */
    addTimeSheet(specializations) {
        let time_sheet = new TimeSheet();

        if(specializations.length > 0) {
            time_sheet.castSpecializationData(specializations);
        }

        this.time_sheets.push(time_sheet);
    }

    /**
     * Delete time sheet row
     */
    removeTimeSheet(index){
        this.time_sheets.splice(index, 1);
    }

    /**
     * Fetch related day sheets
     */
    getRelatedSheets() {
        let scopes = ['default', 'locks', 'owner', 'appointments', 'limitations'];
        let method = this.getOption('methods.related');
        let route = this.getRoute('related');
        let params = this.getRouteParameters();
        let url    = this.getUrlWithQueryParams(route, params, null, scopes);
        let data;

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
    * Delete day sheet list
    *
    * @param {array} list
    *
    * @return Promise
    */
   deleteList(list) {
    let method = this.getOption('methods.create');
    let route  = this.getRoute('deleteList');
    let params = this.getRouteParameters();
    let url    = this.getURL(route, params);
    let data   = {
        id: list,
    };

    return this.getRequest({method, url, data}).send().then((response) => {
        return Promise.resolve(response.response.data);
    });
}

    /*
     * Get unique time sheet specialization field list
     *
     * @param string value
     *
     * @return array
     */
    getTimeSpecializationValue(value) {
        let result = [];

        _.each(this.doctor.time_sheets, (timeSheet) => {
            _.each(timeSheet.specializations, (item) => {
                let field = item[value];
                if(result.indexOf(field) === -1) {
                    result.push(field);
                }
            });
        });
        return result;
    }

    /**
     * Get day sheet specializations names
     *
     * @returns {array}
     */
    get day_sheet_specializations() {
        return this.getTimeSpecializationValue('name').join(', ');
    }

    /**
     * Get day sheet limitations for specializations
     *
     * @returns {array}
     */
    get limitation_data() {
        let specializations = this.getTimeSpecializationValue('id');
        let result = [];

        this.doctor.limitations.forEach((limitation) => {
            if(specializations.indexOf(limitation.specialization_id) !== -1) {
                result.push(limitation);
                if(this.getCurrentLimitationPercent(limitation) > limitation.limitation_percent) {
                    this._isOutOfLimit = true;
                }
            }
        });
        return result;
    }

    /**
     * Calculate doctor limitation percent for specialization
     *
     * @param object item
     *
     * @return float
     */
    getCurrentLimitationPercent(item) {
        if (item.specialization_is_first > 0) {
            return ((item.doctor_is_first_total / item.specialization_is_first) * 100).toFixed(2);
        }
        return 100;
    }

    /**
     * Get week day
     *
     * @returns {string}
     */
    get weekDay() {
        return dateFormat(this.date, 'dd');
    }
}


export default DaySheet;
