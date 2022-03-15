import BaseRepository from './base-repository';

class ProxyRepository extends BaseRepository
{
    /**
     * Constructor
     * 
     * @param {function} callback
     * @patam {object} options
     */ 
    constructor(callback, options = {}) {
        super(options);
        this._callback = callback;
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
    fetch(filters = {}, sort = [], scopes = null, page = 1, limit = 50) {
        return this._callback({
            filters, 
            sort, 
            page, 
            limit, 
            ...(scopes !== null ? {scopes} : {}),
        }, false, this);
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
    fetchList(filters = {}, sort = [], limit = null) {
        return this._callback({
            filters, 
            sort, 
            limit,
        }, true, this);
    }
}

export default ProxyRepository;