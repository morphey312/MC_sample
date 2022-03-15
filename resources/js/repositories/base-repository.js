import BaseModel from '@/models/base-model';
import Pagination from '@/repositories/pagination';
import store from '@/store';
import wait from '@/services/deferred-response';

const qs = require('qs');

class BaseRepository
{
    /**
     * Constructor
     *
     * @param {object} options
     */
    constructor(options = {}) {
        this.endpoint = null;
        this._options = options;
        this._watchers = {};
        this._source = null;
    }

    /**
     * Transform response row to model
     *
     * @param {object} row
     *
     * @returns {BaseModel}
     */
    transformRow(row) {
        return new BaseModel(row);
    }

    /**
     * Get endpoint URL
     *
     * @param {string} path
     *
     * @returns {string}
     */
    getEndpoint(path = null) {
        if (this.endpoint === null) {
            throw 'Base endpoint is not specified';
        }
        if (path === null) {
            return this.endpoint
        }
        return [this.endpoint, path].join('/');
    }

    /**
     * Build URL for request
     *
     * @param {string} path
     * @param {object} params May contain props: {object} filters, {array} sort, {array} scopes, {number} page, {number} limit
     *
     * @returns {string}
     */
    buildUrl(path = null, params = {}) {
        let endpoint = this.getEndpoint(path);
        let queryString = qs.stringify(params);
        return `${endpoint}?${queryString}`;
    }

    /**
     * Fetch data
     *
     * @param {object} filters
     * @param {array} sort
     * @param {array} scopes
     * @param {number} page
     *
     * @returns {Promise}
     */
    fetch(filters = null, sort = null, scopes = null, page = 1, limit = 50) {
        let postData = null;
        let query = {
            page,
            limit
        }

        if (this._options.forcePost === true) {
            postData = {
                ...this.getFilters(filters),
                ...this.getSort(sort),
                ...this.getScopes(scopes)
            };
        } else {
            query = {
                ...this.getFilters(filters),
                ...this.getSort(sort),
                ...this.getScopes(scopes),
                page,
                limit
            }
        }

        return this.fetchInternal(this.buildUrl(null, query), true, postData);
    }

    /**
     * Fetch data iteratively
     *
     * @param {object} filters
     * @param {array} sort
     * @param {array} scopes
     * @param {number} chunkSize
     *
     * @returns {Promise}
     */
    async iterate(filters = null, sort = null, scopes = null, chunkSize = 500) {
        let result = [];
        for (let i = 1;; i++) {
            let page = await this.fetch(filters, sort, scopes, i, chunkSize);
            result = result.concat(page.rows);
            if (page.pagination.last_page <= i) {
                break;
            }
        }
        return Promise.resolve(result);
    }

    /**
     * Fetch data from given URL
     *
     * @param {string} url
     * @param {bool} pagination
     * @param {object} postData
     *
     * @returns {Promise}
     */
    fetchInternal(url, pagination = true, postData = null) {
        let result = null;
        let cancelToken = this.createCancelToken();

        if (postData) {
            result = axios.post(url, {...postData, cancelToken});
        } else {
            result = axios.get(url, {cancelToken});
        }

        return result.then((response) => {
            if (response.data.promiseId !== undefined) {
                return wait(response.data.promiseId).then((response) => {
                    return this.createResponse(response, pagination);
                })
            }
            return this.createResponse(response, pagination);
        }).finally(() => {
            this.discardCancelToken(cancelToken);
        });
    }

    /**
     * Create a response
     *
     * @param {object} response
     * @param {bool} pagination
     *
     * @returns {object}
     */
    createResponse(response, pagination = true) {
        let data = (pagination ? response.data : response).data.map((row) => this.transformRow(row));
        this.notifyWatcher('data', data);
        return (pagination ? {rows: data, pagination: new Pagination(response.data.meta)} : data);
    }

    /**
     * Generate empty result
     *
     * @returns {Promise}
     */
    emptyData(pagination = true) {
        return Promise.resolve(pagination ? {
            rows: [],
            pagination: new Pagination({
                current_page: 1,
                from: null,
                last_page: 1,
                per_page: 50,
                to: null,
                total: 0,
            }),
        } : []);
    }

    /**
     * Fetch lists
     *
     * @param {object} filters
     * @param {array} sort
     * @param {number} limit
     *
     * @returns {Promise}
     */
    fetchList(filters = null, sort = null, limit = null) {
        let url = this.buildUrl('all', {
            ...this.getFilters(filters),
            ...this.getSort(sort),
            limit,
        });

        let result = this.fetchListInternal(url);

        return result.then((data) => {
            this.notifyWatcher('data', data);
            return data;
        });
    }

    /**
     * Fetch list from given url
     *
     * @param {string} url
     *
     * @returns {Promise}
     */
    fetchListInternal(url) {
        let cancelToken = this.createCancelToken();
        return axios.get(url, {cancelToken})
            .then((response) => {
                return response.data;
            }).finally(() => {
                this.discardCancelToken(cancelToken);
            });
    }

    /**
     * Get value for filters
     *
     * @param {object} filters
     *
     * @returns {object}
     */
    getFilters(filters) {
        let result = {}

        if (filters !== null || this._options.filters !== undefined) {
            result = {filters: {
                ...(this._options.filters || {}),
                ...(filters || {}),
            }};
        }

        if (this._options.limitClinics === true) {
            let clinic = store.state.user.workingClinics;
            clinic.length === 0 ? clinic = null : null
            let newFilters = result.filters || {};
            let selectedClinic = _.castArray(newFilters.clinic || []);
            if (selectedClinic.length !== 0) {
                clinic = _.intersection(clinic, selectedClinic);
            }
            result.filters = {
                ...newFilters,
                clinic,
            };
        }

        return result;
    }

    /**
     * Get value for sort
     *
     * @param {array} sort
     *
     * @returns {object}
     */
    getSort(sort) {
        if (sort !== null) {
            return {sort};
        }
        if (this._options.sort !== undefined) {
            return {sort: this._options.sort};
        }
        return {};
    }

    /**
     * Get value for scopes
     *
     * @param {array} scopes
     *
     * @returns {object}
     */
    getScopes(scopes) {
        if (scopes !== null) {
            return {scopes};
        }
        if (this._options.scopes !== undefined) {
            return {scopes: this._options.scopes};
        }
        return {};
    }

    /**
     * Watch options changes
     *
     * @param {string|array} what
     * @param {function} callback
     */
    watch(what, callback) {
        if (_.isArray(what)) {
            what.forEach((w) => {
                this.watch(w, callback);
            });
        } else {
            if (this._watchers[what] === undefined) {
                this._watchers[what] = [];
            }
            this._watchers[what].push(callback);
        }
    }

    /**
     * Unwatch options changes
     *
     * @param {string|array} what
     * @param {function} callback
     */
    unwatch(what, callback = null) {
        if (_.isArray(what)) {
            what.forEach((w) => {
                this.unwatch(w, callback);
            });
        } else if (this._watchers[what] !== undefined) {
            if (callback !== null) {
                _.pull(this._watchers[what], callback);
            } else {
                delete this._watchers[what];
            }
        }
    }

    /**
     * Send notification to watchers
     *
     * @param {string} prop
     * @param {*} value
     */
    notifyWatcher(prop, value) {
        if (this._watchers[prop] !== undefined) {
            this._watchers[prop].forEach((fn) => {
                fn(this, prop, value);
            });
        }
    }

    /**
     * Change repository options
     *
     * @param {object} opts
     * @param {bool} notify
     */
    setOptions(opts, notify = true) {
        for (let prop in opts) {
            if (!_.isEqual(this._options[prop], opts[prop])) {
                this._options[prop] = opts[prop];
                if (notify) {
                    this.notifyWatcher(prop, opts[prop]);
                }
            }
        }
    }

    /**
     * Get current options
     *
     * @returns {object}
     */
    getOptions() {
        return this._options;
    }

    /**
     * Set default filters
     *
     * @param {object} filters
     * @param {bool} merge
     * @param {bool} notify
     */
    setFilters(filters, merge = false, notify = true) {
        if (merge && this._options.filters !== undefined) {
            filters = {...this._options.filters, ...filters};
        }
        this.setOptions({filters}, notify);
    }

    /**
     * Set default sort
     *
     * @param {array} sort
     * @param {bool} notify
     */
    setSort(sort, notify = true) {
        this.setOptions({sort}, notify);
    }

    /**
     * Set default scopes
     *
     * @param {array} scopes
     * @param {bool} notify
     */
    setScopes(scopes, notify = true) {
        this.setOptions({scopes}, notify);
    }

    /**
     * Cancel last request
     */
    cancelRequest() {
        if (this._source !== null) {
            this._source.cancel('Cancelled');
        }
    }

    /**
     * Create token
     *
     * @returns {string}
     */
    createCancelToken() {
        this._source = axios.CancelToken.source();
        return this._source.token;
    }

    /**
     * Forget token
     *
     * @param {string} token
     */
    discardCancelToken(token) {
        if (this._source !== null && token === this._source.token) {
            this._source = null;
        }
    }
}

export default BaseRepository;

