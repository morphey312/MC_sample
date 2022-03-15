import lts from '@/services/lts';
import moment from 'moment';
import eventHub from '@/services/event-hub';
import logger from '@/services/logging';

const STATE_OFFLINE = 'offline';
const STATE_ONLINE = 'online';
const STATE_BUSY = 'busy';
const STATE_WRAP_UP = 'wrap-up';
const STATE_AWAY = 'away';
const STATE_CONFERENCE = 'conference';

const ACTION_SESSION_START = 'session-start';
const ACTION_SESSION_END = 'session-end';
const ACTION_PAUSE_START = 'pause-start';
const ACTION_PAUSE_END = 'pause-end';
const ACTION_CALL_START = 'call-start';
const ACTION_CALL_END = 'call-end';
const ACTION_CONFERENCE_START = 'conference-start';
const ACTION_CONFERENCE_END = 'conference-end';
const ACTION_WRAPUP_START = 'wrapup-start';
const ACTION_WRAPUP_END = 'wrapup-end';

class State
{
    /**
     * Constructor
     * 
     * @param {string} name
     */ 
    constructor(name) {
        this._name = name;
        this._transitions = [];
    }
    
    /**
     * Add transition
     * 
     * @param {string} to
     * @param {function} callback
     * 
     * @returns {State}
     */ 
    addTransition(to, callback) {
        this._transitions.push({
            to,
            callback,
        });
        return this;
    }
    
    /**
     * Get transition if exists
     * 
     * @return {object}
     */ 
    getTransition(to) {
        return _.find(this._transitions, (trans) => trans.to === to);
    }
    
    /**
     * Get name
     * 
     * @returns {string}
     */ 
    get name() {
        return this._name;
    }
    
    /**
     * Get transitions
     * 
     * @returns {array}
     */ 
    get transitions() {
        return this._transitions;
    }
}

class StateManager
{
    /**
     * Constructor
     */ 
    constructor(ua) {
        this._ua = ua;
        
        // Setup offline state
        const offline = new State(STATE_OFFLINE);
        offline
            .addTransition(STATE_ONLINE, () => {
                this.sessionStarted();
            });
        
        // Setup online state
        const online = new State(STATE_ONLINE);
        online
            .addTransition(STATE_OFFLINE, () => {
                this.sessionEnded();
            })
            .addTransition(STATE_BUSY, () => {
                this.callStarted();
            })
            .addTransition(STATE_AWAY, () => {
                this.pauseStarted();
            })
            .addTransition(STATE_WRAP_UP, () => {
                this.startProcessEnquiry();
            });
        
        // Setup busy state
        const busy = new State(STATE_BUSY);
        busy
            .addTransition(STATE_WRAP_UP, () => {
                this.callEnded();
            })
            .addTransition(STATE_CONFERENCE, () => {
                this.conferenceStarted();
            })
            .addTransition(STATE_OFFLINE, () => {
                this.callEnded();
                this.sessionEnded();
            });
        
        // Setup wrap-up state
        const wrapup = new State(STATE_WRAP_UP);
        wrapup
            .addTransition(STATE_BUSY, () => {
                this.callStarted();
            })
            .addTransition(STATE_ONLINE, () => {
                this.operatorReady();
            })
            .addTransition(STATE_OFFLINE, () => {
                this.operatorReady();
                this.sessionEnded();
            });
        
        // Setup away state
        const away = new State(STATE_AWAY);
        away
            .addTransition(STATE_ONLINE, () => {
                this.pauseEnded();
            })
            .addTransition(STATE_OFFLINE, () => {
                this.pauseEnded();
                this.sessionEnded();
            })
            .addTransition(STATE_BUSY, () => {
                this.pauseEnded();
                this.callStarted();
            })
            .addTransition(STATE_WRAP_UP, () => {
                this.pauseEnded();
                this.startProcessEnquiry();
            });
        
        // Setup conference state
        const conference = new State(STATE_CONFERENCE);
        conference
            .addTransition(STATE_BUSY, () => {
                this.conferenceEnded();
            })
            .addTransition(STATE_WRAP_UP, () => {
                this.conferenceEnded();
                this.callEnded();
            })
            .addTransition(STATE_OFFLINE, () => {
                this.conferenceEnded();
                this.callEnded();
                this.sessionEnded();
            });
        
        this._states = [
            offline,
            online,
            busy,
            wrapup,
            away,
            conference,
        ];
        this._state = offline;
        this._stack = [];
        this._sessionStartTime = null;
        this._pauseStartTime = null;
        this._callStartTime = null;
        this._conferenceStartTime = null;
        this._wrapupStartTime = null;
        this._callTotalDuration = 0;
        this._pauseTotalDuration = 0;
        this._pauseTotalCount = 0;
        
        this.recoverSession();
    }
    
    /**
     * Serialize session state
     * 
     * @returns {object}
     */ 
    serializeSession() {
        return {
            state: this._state.name,
            timeStarted: this._sessionStartTime ? this._sessionStartTime.getTime() : 0,
            callDuration: this._callTotalDuration,
            pauses: this._pauseTotalCount,
            timePaused: this._pauseStartTime ? this._pauseStartTime.getTime() : 0,
            pauseDuration: this._pauseTotalDuration,
        };
    }
    
    /**
     * Unserialize session
     * 
     * @param {object} data
     */
    unserializeSession(data) {
        this._sessionStartTime = new Date(data.timeStarted);
        this._callTotalDuration = data.callDuration;
        this._pauseTotalCount = data.pauses;
        this._pauseStartTime = data.timePaused ? new Date(data.timePaused) : null;
        this._pauseTotalDuration = data.pauseDuration;
        this._state = this.findState(data.state);
    }
     
    /**
     * Restore previously started session if possible
     */ 
    recoverSession() {
        let prev = lts.sipSession;
        if (prev && prev.timeStarted) {
            let started = moment(prev.timeStarted);
            if (started.isSameOrAfter(moment().startOf('day'))) {
                this.unserializeSession(prev);
                this.fixSession();
            }
        }
    }
    
    /**
     * Fix session after it has been recovered
     */ 
    fixSession() {
        if (this._state === undefined) {
            // Something is wrong, just start from offline state
            this.resetSession();
        } else {
            switch (this._state.name) {
                case STATE_OFFLINE:
                    // Something is wrong, this should never happen,
                    // but just in case we will trigger session termination
                    this.sessionEnded();
                    this.commitState();
                    break;
                case STATE_BUSY:
                case STATE_CONFERENCE:
                    // Was in call, change state to wrap-up
                    this.transit(STATE_WRAP_UP);
                    break;
            }
        }
    }
    
    /**
     * Reset session stats
     */ 
    resetSession() {
        this._state = this.findState(STATE_OFFLINE);
        this._sessionStartTime = null;
        this._pauseStartTime = null;
        this._callStartTime = null;
        this._conferenceStartTime = null;
        this._wrapupStartTime = null;
        this._callTotalDuration = 0;
        this._pauseTotalDuration = 0;
        this._pauseTotalCount = 0;
        delete lts.sipSession;
    }
    
    /**
     * Find state by name
     * 
     * @param {string} name
     * 
     * @returns {State}
     */ 
    findState(name) {
        return _.find(this._states, (state) => state.name === name);
    }
    
    /**
     * Transit to another state
     * 
     * @param {string} to
     * 
     * @returns {bool}
     */ 
    transit(to) {
        let transition = this._state.getTransition(to);
        let newState = this.findState(to);
        if (transition !== undefined && newState !== undefined) {
            transition.callback();
            eventHub.$emit('operator:state-changed', {
                from: this._state.name,
                to: newState.name,
            });
            logger.log('Operator state change', {
                from: this._state.name,
                to: newState.name,
            });
            this._state = newState;
            this.commitState();
            return true;
        }
        return false;
    }
    
    /**
     * Check if we can transit to certain state
     * 
     * @param {string} to
     * 
     * @returns {bool}
     */ 
    canTransit(to) {
        return this._state.getTransition(to) !== undefined;
    }
    
    /**
     * Function gets called when session started
     */ 
    sessionStarted() {
        this._sessionStartTime = new Date();
        this.pushAction(ACTION_SESSION_START);
    }
    
    /**
     * Function gets called when session ended
     */ 
    sessionEnded() {
        let duration = this.calcTime(this._sessionStartTime);
        this.pushAction(ACTION_SESSION_END, duration);
        this.resetSession();
    }
    
    /**
     * Function gets called when call started
     */ 
    callStarted() {
        this._callStartTime = new Date();
        this.pushAction(ACTION_CALL_START);
    }
    
    /**
     * Function gets called when call ended
     */ 
    callEnded() {
        let duration = this.calcTime(this._callStartTime);
        this._callTotalDuration += duration;
        this.pushAction(ACTION_CALL_END, duration);
        this._callStartTime = null;
        this._wrapupStartTime = new Date();
        this.pushAction(ACTION_WRAPUP_START);
    }
    
    /**
     * Function gets called when site enquiry process started
     */ 
    startProcessEnquiry() {
        this._wrapupStartTime = new Date();
        this.pushAction(ACTION_WRAPUP_START);
    }
    
    /**
     * Function gets called when pause started
     */ 
    pauseStarted() {
        this._pauseStartTime = new Date();
        this._pauseTotalCount++;
        this.pushAction(ACTION_PAUSE_START);
    }
    
    /**
     * Function gets called when pause ended
     */ 
    pauseEnded() {
        let duration = this.calcTime(this._pauseStartTime);
        this._pauseTotalDuration += duration;
        this.pushAction(ACTION_PAUSE_END, duration);
        this._pauseStartTime = null;
    }
    
    /**
     * Function gets called when conference started
     */ 
    conferenceStarted() {
        this._conferenceStartTime = new Date();
        this.pushAction(ACTION_CONFERENCE_START);
    }
    
    /**
     * Function gets called when conference ended
     */ 
    conferenceEnded() {
        let duration = this.calcTime(this._conferenceStartTime);
        this.pushAction(ACTION_CONFERENCE_END, duration);
        this._conferenceStartTime = null;
    }
    
    /**
     * Function gets called when operator is ready
     */ 
    operatorReady() {
        let duration = this.calcTime(this._wrapupStartTime);
        this.pushAction(ACTION_WRAPUP_END, duration);
        this._wrapupStartTime = null;
    }
    
    /**
     * Finalize state
     */
    commitState() {
        if (this._stack.length !== 0) {
            this.submitState(this._stack);
            this._stack = [];
        }
        if (this._state.name !== STATE_OFFLINE) {
            lts.sipSession = this.serializeSession();
        }
    }
    
    /**
     * Send statuses to the server
     * 
     * @param {array} stack
     */
    submitState(actions) {
        return axios.post('/api/v1/session-logs', {
            actions,
        });
    }
    
    /**
     * Push status to the stack
     * 
     * @param {string} action
     * @param {number} duration
     */ 
    pushAction(action, duration = 0) {
        this._stack.push({
            sip: this.getSIP(),
            phone_number: this.getPhoneNumber(),
            action,
            duration: parseInt(duration / 1000),
        });
    }
    
    /**
     * Get related SIP
     * 
     * @returns {string}
     */ 
    getSIP() {
        return this._ua.number;
    }
    
    /**
     * Get related phone number
     * 
     * @returns {string|null}
     */ 
    getPhoneNumber() {
        if (this._ua.call !== null) {
            return this._ua.call.info.phoneNumber;
        }
        return null;
    }
    
    /**
     * Calculate past time
     * 
     * @param {Date} since
     * 
     * @returns {number}
     */ 
    calcTime(since) {
        return since === null ? 0 : (Date.now() - since.getTime());
    }
    
    /**
     * Get current state
     * 
     * @returns {State}
     */ 
    get state() {
        return this._state;
    }
    
    /**
     * Get session start time
     * 
     * @returns {Date}
     */ 
    get sessionStartTime() {
        return this._sessionStartTime;
    }
    
    /**
     * Get total calls duration
     * 
     * @returns {number}
     */ 
    get callTotalDuration() {
        return this._callTotalDuration + this.currentCallDuration;
    }
    
    /**
     * Get total pauses duration
     * 
     * @returns {number}
     */ 
    get pauseTotalDuration() {
        return this._pauseTotalDuration + this.currentPauseDuration;
    }
    
    /**
     * Get current call duration
     * 
     * @returns {number}
     */ 
    get currentCallDuration() {
        return this.calcTime(this._callStartTime);
    }
    
    /**
     * Get current pause duration
     * 
     * @returns {number}
     */ 
    get currentPauseDuration() {
        return this.calcTime(this._pauseStartTime);
    }
    
    /**
     * Get total number of pauses
     * 
     * @returns {number}
     */ 
    get pauseTotalCount() {
        return this._pauseTotalCount;
    }
    
    /**
     * Check if current state is "paused"
     * 
     * @returns {bool}
     */ 
    get isPaused() {
        return this._state.name === STATE_AWAY;
    }
    
    /**
     * Check if current state is "busy"
     * 
     * @returns {bool}
     */ 
    get isBusy() {
        return this._state.name === STATE_BUSY;
    }
    
    /**
     * Check if current state is "busy" or "conference"
     * 
     * @returns {bool}
     */
    get isInCall() {
        return this._state.name === STATE_BUSY
            || this._state.name === STATE_CONFERENCE;
    }
}

export {
    StateManager,
    STATE_OFFLINE,
    STATE_ONLINE,
    STATE_BUSY,
    STATE_WRAP_UP,
    STATE_AWAY,
    STATE_CONFERENCE,
};