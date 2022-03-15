class ParkedCall
{
    /**
     * Constructor
     * 
     * @param {CallInfo} call
     * @param {string} lot
     * @param {string} space
     */ 
    constructor(call, lot, space) {
        this._call = call;
        this._lot = lot;
        this._space = space;
        this._since = new Date();
    }
    
    /**
     * Get call
     * 
     * @returns {CallInfo}
     */ 
    get call() {
        return this._call;
    }
    
    /**
     * Get number
     * 
     * @returns {string}
     */ 
    get number() {
        return this._call.phoneNumber;
    }
    
    /**
     * Get lot
     * 
     * @returns {string}
     */ 
    get lot() {
        return this._lot;
    }
    
    /**
     * Get space
     * 
     * @returns {string}
     */ 
    get space() {
        return this._space;
    }
    
    /**
     * Get waiting time
     * 
     * @returns {number}
     */ 
    get waitingTime() {
        return Date.now() - this._since.getTime();
    }
    
    /**
     * Get caller name
     * 
     * @returns {string}
     */ 
    get name() {
        return this._call.versaName;
    }
}

export default ParkedCall;