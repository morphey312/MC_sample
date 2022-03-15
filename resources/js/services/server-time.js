import moment from 'moment';

class ServerTime
{
    /**
     * Constructor
     */ 
    constructor() {
        this.difference = moment.duration(0);
    }
    
    /**
     * Sync local and server time
     * 
     * @param {string} time
     */ 
    sync(time) {
        this.difference = moment.duration(moment.parseZone().utcOffset() - moment.parseZone(time).utcOffset(), 'm');
    }
    
    /**
     * Get current server time
     * 
     * @returns {object}
     */ 
    now() {
        return moment().subtract(this.difference);
    }
    
    /**
     * Get current server time as string
     * 
     * @returns {string}
     */ 
    asString() {
        return this.now().format('YYYY-MM-DD HH:mm:ss');
    }
}

export default new ServerTime();