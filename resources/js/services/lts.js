const KEYS = '_keys';

class Container
{
    /**
     * Constructor
     * 
     * @param {object} data
     */ 
    constructor(data = {}) {
        this._data = data;
    }
    
    /**
     * Store item
     * 
     * @param {string} key
     * @param {*} value
     */
    store(key, value) {
        this._data[key] = value;
    }
    
    /**
     * Retrieve item
     * 
     * @param {string} key
     * 
     * @returns {*}
     */
    retrieve(key) {
        return this._data[key];
    }
    
    /**
     * Check container has item 
     * 
     * @param {string} key
     * 
     * @returns {bool}
     */
    has(key) {
        return key in this._data;
    }
    
    /**
     * Remove item from container
     * 
     * @param {string} key
     */ 
    remove(key) {
        delete this._data[key];
    }
    
    /**
     * Return items keys
     * 
     * @returns {array}
     */ 
    keys() {
        return Object.keys(this._data);
    }
}

class LTS extends Container
{
    /**
     * Constructor
     * 
     * @param {string} prefix 
     */ 
    constructor(prefix = '') {
        super();
        this._prefix = prefix;
        this.restore();
    }
    
    /**
     * Restore data from storage
     */ 
    restore() {
        let keys = [];
        try {
            keys = JSON.parse(localStorage.getItem(this._prefix + KEYS)) || [];
        } catch (e) {}
        for (let key of keys) {
            try {
                this._data[key] = JSON.parse(localStorage.getItem(this._prefix + key));
            } catch (e) {}
        }
    }
    
    /**
     * @inheritdoc
     */
    store(key, value) {
        let isNew = !this.has(key);
        super.store(key, value);
        localStorage.setItem(this._prefix + key, JSON.stringify(value));
        if (isNew) {
            localStorage.setItem(this._prefix + KEYS, JSON.stringify(this.keys()));
        }
    }
    
    /**
     * @inheritdoc
     */ 
    remove(key) {
        if (this.has(key)) {
            super.remove(key);
            localStorage.removeItem(this._prefix + key);
            localStorage.setItem(this._prefix + KEYS, JSON.stringify(this.keys()));
        }
    }
}

export default new Proxy(new LTS('__lts_'), {
    get(target, key) {
        return target.retrieve(key);
    },
    set(target, key, value) {
        target.store(key, value);
        return true;
    },
    has(target, key) {
        return target.has(key);
    },
    deleteProperty(target, key) {
        target.remove(key);
        return true;
    },
});