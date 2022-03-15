import BaseRepository from '@/repositories/base-repository';

class LaboratoryClientRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'name', direction: 'asc'}],
            ...options
        });
        this.endpoint = '/api/v1/mc-lab';
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
     fetchList(filters = null, sort = null, limit = null, listName = null) {
        let url = this.buildUrl(listName, {
            ...this.getFilters(filters), 
            ...this.getSort(sort),
            limit,
        });
        
        let result = this.fetchListInternal(url);
        
        return result.then((data) => {
            return data;
        }).catch(e => {
            console.error(e);
        });
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
      fetch(filters = null, sort = null, scopes = null, page = 1, limit = 50, listName = null) {
        return this.fetchInternal(this.buildUrl(listName, {
            ...this.getFilters(filters), 
            ...this.getSort(sort), 
            ...this.getScopes(scopes), 
            page, 
            limit, 
        }));
    }
}

export default LaboratoryClientRepository;