class TabSync
{
    /**
     * Constructor
     */ 
    constructor() {
        this.listeners = {};
        if (window.BroadcastChannel !== undefined) { 
            this.isPrimary = undefined;
            this.echoing = false;
            this.channel = new BroadcastChannel('sync_channel');
            this.channel.onmessage = (event) => {
                this.propagate(event.data.key, event.data.data);
            }
            this.on('tabecho', (counter) => {
                this.countEcho();
            });
            this.sendEcho();
        } else {
            this.isPrimary = true;
            this.channel = null;
        }
    }
    
    /**
     * Send echo to other tabs
     */ 
    sendEcho() {
        this.echoing = true;
        this.sync('tabecho', 1);
        setTimeout(() => {
            this.echoing = false;
            if (this.isPrimary === undefined) {
                this.isPrimary = true;
            }
        }, 3000);
    }
    
    /**
     * Count echoes from other tabs
     */ 
    countEcho() {
        if (this.isPrimary === undefined) {
            this.isPrimary = false;
        } else if (!this.echoing && this.isPrimary === true) {
            this.sendEcho();
        }
    }
    
    /**
     * Sync data between tabs
     * 
     * @param {string} key
     * @param {*} data
     */ 
    sync(key, data) {
        if (this.channel !== null) {
            this.channel.postMessage({key, data});
        }
    }
    
    /**
     * Set data listener
     * 
     * @param {string} key
     * @param {function} callback
     */ 
    on(key, callback) {
        if (this.listeners[key] === undefined) {
            this.listeners[key] = [];
        }
        this.listeners[key].push(callback);
    }
    
    /**
     * Unset data listener
     * 
     * @param {string} key
     * @param {function} callback
     */ 
    off(key, callback) {
        if (this.listeners[key] !== undefined) {
            _.remove(this.listeners[key], callback);
            if (this.listeners[key].length === 0) {
                delete this.listeners[key];
            }
        }
    }
    
    /**
     * Deliver data to listeners
     * 
     * @param {string} key
     * @param {*} data
     */ 
    propagate(key, data) {
        if (this.listeners[key] !== undefined) {
            this.listeners[key].forEach((listener) => {
                listener(data, key);
            });
        }
    }
    
    /**
     * Get number of echoes
     * 
     * @returns {number}
     */ 
    get isPrimaryTab() {
        return this.isPrimary;
    }
}

export default new TabSync();