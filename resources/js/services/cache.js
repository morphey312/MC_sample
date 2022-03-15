class Cache
{
    /**
     * Constructor
     */ 
    constructor() {
        this._entries = {};
    }
    
    /**
     * Try to get data from cache
     * 
     * @param {string} key
     * @param {function} fallback - fallback function, must return a Promise object
     * 
     * @returns {Promise}
     */ 
    get(key, fallback) {
        if (key in this._entries) {
            let data = this._entries[key];
            if (data instanceof Promise) {
                return data;
            }
            return Promise.resolve(data);
        }
        
        return this._entries[key] = fallback().then((data) => {
            this._entries[key] = data;
            return data;
        });
    }
    
    /**
     * Remove an entry from cache
     * 
     * @param {string|function} key
     */ 
    forget(key) {
        if (_.isFunction(key)) {
            Object.keys(this._entries).forEach((k) => {
                if (key(k)) {
                    delete this._entries[k];
                }
            });
        } else {
            delete this._entries[key];
        }
    }
    
    /**
     * Remove all entries from the cache
     */ 
    flush() {
        this._entries = {};
    }
}

export default new Cache();