class Pagination
{
    /**
     * Constructor
     * 
     * @param {object} data
     */ 
    constructor(data) {
        this._data = data;
    }
    
    /**
     * Get current page number
     * 
     * @return {Number}
     */ 
    get current_page() {
        return this._data.current_page;
    }
    
    /**
     * Get last page number
     * 
     * @return {Number}
     */
    get last_page() {
        return this._data.last_page;
    }
    
    /**
     * Get from
     * 
     * @return {Number}
     */
    get from() {
        return this._data.from;
    }
    
    /**
     * Get to
     * 
     * @return {Number}
     */
    get to() {
        return this._data.to;
    }

    /**
     * Get page size
     * 
     * @return {Number}
     */
    get per_page() {
        return this._data.per_page;
    }
    
    /**
     * Get total count
     * 
     * @return {Number}
     */
    get total() {
        return this._data.total;
    }
}

export default Pagination;