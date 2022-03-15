import lts from '@/services/lts';
import moment from 'moment';
import {dateFormat} from '@/services/format';

class ShiftManager
{
    constructor() {
        this._session_id = null;
        this._start = null;
        this._end = null;
        this._endpoint = '/api/v1/cashier-session-logs';
        this.recoverSession();
    }

    /**
     * Restore previously started session if possible
     */ 
    recoverSession() {
        let prev = lts.cashierSession;
        if (prev && prev.start) {
            let started = moment(prev.start);
            if (started.isSameOrAfter(moment().startOf('day')) || prev.end == null) {
                this.unserializeSession(prev);
            }
        }
    }

    /**
     * Unserialize session
     * 
     * @param {object} data
     */
    unserializeSession(data) {
        this._start = data.start;
        this._session_id = data.id;
        this._end = data.end;
    }

    /**
     * Serialize session state
     * 
     * @returns {object}
     */ 
    serializeSession() {
        return {
            start: this._start,
            end: this._end,
            id: this._session_id,
        };
    }

    /**
     * Function gets called when session started
     * 
     * @returns {Promise}
     */ 
    sessionStarted() {
        this._start = this.getActionTime();
        return this.createSession();
    }

    /**
     * Function gets called when session ended
     * 
     * @returns {Promise}
     */ 
    sessionEnded() {
        this._end = this.getActionTime();
        return this.updateSession();
    }

    /**
     * Get formatted datetime
     * 
     * @returns {String}
     */
    getActionTime() {
        return dateFormat(new Date(), 'YYYY-MM-DD HH:mm:ss');
    }

    /**
     * Create session
     * 
     * @returns {Promise}
     */
    createSession() {
        return axios.post(this.endpoint, this.serializeSession())
                    .then((response) => {
                        this.unserializeSession(response.data);
                        lts.cashierSession = this.serializeSession();
                        return Promise.resolve();
                    });
    }

    /**
     * Update session
     * 
     * @returns {Promise}
     */
    updateSession() {
        return axios.put(this.endpoint, this.serializeSession())
                    .then(() => {
                        this.destroy();
                        return Promise.resolve();
                    });
    }
    
    /**
     * Destroy ShiftManager
     */
    destroy() {
        this.clearSession();
        delete lts.cashierSession;
    }

    /**
     * Clear session
     */
    clearSession() {
        this._session_id = null;
        this._start = null;
        this._end = null;
    }

    /**
     * Get session id
     * 
     * @returns {number}
     */
    get id() {
        return this._session_id;
    }

    /**
     * Get endpoint
     * 
     * @returns {String}
     */
    get endpoint() {
        return this._endpoint + (this.id ? ('/' + this.id) : '');
    }
}

const shiftManager = new ShiftManager();

export default shiftManager;