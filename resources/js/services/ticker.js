class Ticker
{
    /**
     * Constructor
     */ 
    constructor() {
        this.interval = null;
        this.listeners = [];
        this.epoch = 0;
        this.start();
    }
    
    /**
     * Call all listeners
     */ 
    tick() {
        this.epoch++;
        this.listeners.forEach((item) => {
            let age = this.epoch - item.since;
            if (age % item.period === 0) {
                item.listener(age);
            }
        });
    }
    
    /**
     * Add listener
     * 
     * @param {function} listener
     * @param {int} period
     */ 
    on(listener, period = 1) {
        this.off(listener);
        this.listeners.push({
            listener, 
            period,
            since: this.epoch,
        });
    }
    
    /**
     * Remove listener
     * 
     * @param {function} listener
     */ 
    off(listener) {
        _.remove(this.listeners, (item) => item.listener === listener);
    }
    
    /**
     * Start ticker
     */ 
    start() {
        if (this.interval === null) {
            this.interval = setInterval(() => {
                this.tick()
            }, 1000);
        }
    }
    
    /**
     * Stop ticker
     */ 
    stop() {
        if (this.interval !== null) {
            clearInterval(this.interval);
            this.interval = null;
        }
    }
    
    /**
     * Remove all listeners
     */ 
    clear() {
        this.listeners = [];
    }
}

export default new Ticker();