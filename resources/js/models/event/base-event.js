class BaseEvent 
{
    /**
     * Constructor
     */ 
    constructor(data) {
        this._data = data;
        this._id = data.id;
        this._start = null;
        this._end = undefined;
        this._title = undefined;
    }
    
    /**
     * Get event id
     * 
     * @returns {int|string}
     */ 
    get id() {
        return this._id;
    }
    
    /**
     * Get event start date
     * 
     * @returns {Date}
     */
    get startDate() {
        return this._start;
    }
    
    /**
     * Get event end date
     * 
     * @returns {Date}
     */
    get endDate() {
        return this._end;
    }
    
    /**
     * Get event title
     * 
     * @returns {string}
     */
    get title() {
        return this._title;
    }
    
    /**
     * Get event subject
     * 
     * @returns {*}
     */
    get data() {
        return this._data;
    }
}

export default BaseEvent;