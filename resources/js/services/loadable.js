class Loadable
{
    /**
     * Constructor
     */ 
    constructor() {
        this._data = null;
        this._loading = null;
        this._endpoint = null;
    }
    
    /**
     * Load data
     * 
     * @param {bool} force
     * 
     * @returns {Promise}
     */ 
    load(force = false) {
        if (force === false && this.isLoaded()) {
            return Promise.resolve(this);
        }
        if (this._loading) {
            return this._loading;
        }
        return this._loading = axios.get(this._endpoint).then((response) => {
            this.loadComplete(response.data);
            this._loading = null;
            return Promise.resolve(this);
        })
            .catch((error) => {
                this._loading = null;
                return Promise.reject(error);
            });
    }
    
    /**
     * Clear loaded data
     */ 
    flush() {
        this._data = null;
    }
    
    /**
     * Check if data already loaded
     * 
     * @returns {boolean}
     */ 
    isLoaded() {
        return this._data !== null;
    }
    
    /**
     * Initialize data
     * 
     * @param {*} data
     */ 
    loadComplete(data) {
        this._data = data;
    }
    
    /**
     * Alias for isLoaded
     * 
     * @returns {boolean}
     */ 
    get loaded() {
        return this.isLoaded();
    }
}

export default Loadable;