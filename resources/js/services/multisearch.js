class MultiSearchRequest
{
    /**
     * Constructor
     * 
     * @param {string} url
     * @param {array} scopes
     */ 
    constructor(url, scopes = null) {
        this._url = url;
        this._scopes = scopes;
        this.reset();
    }
    
    /**
     * Clear queues
     */ 
    reset() {
        this._filters = [];
    }
    
    /**
     * Add query to the request
     * 
     * @param {object} filter
     * @param {function} callback
     */ 
    search(filter, callback) {
        this._filters.push({filter, callback});
    }
    
    /**
     * Submit the request
     * 
     * @returns {Promise}
     */ 
    submit() {
        return axios.post(this._url, this.buildBody())
            .then((response) => {
                this.processResponse(response.data);
            });
    }
    
    /**
     * Build request body
     * 
     * @returns {array}
     */ 
    buildBody() {
        return {
            filters: this._filters.map((part) => part.filter),
            ...(this._scopes === null ? {} : {scopes: this._scopes}),
        };
    }
    
    /**
     * Process the response
     * 
     * @param {object} response
     * 
     * @returns {object}
     */ 
    processResponse(response) {
        for (let i = 0; i < this._filters.length && i < response.length; i++) {
            this._filters[i].callback(response[i]);
        }
    }
}

export default MultiSearchRequest;