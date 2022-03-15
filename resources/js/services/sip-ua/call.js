import CallInfo from './call-info';
import ParkedCall from './parked-call';
import eventHub from '@/services/event-hub';
import logger from '@/services/logging';

const STATE_PENDING = 0;
const STATE_IN_PROGRESS = 1;
const STATE_TERMINATED = 2;
const STATE_PARKING = 3;
const STATE_PARKED = 4;

class Call
{
    /**
     * Constructor
     * 
     * @param {SipUA} ua
     * @param {SessionAdapter} session
     */ 
    constructor(ua, session) {
        this._ua = ua;
        this._session = session;
        this._state = STATE_PENDING;
        this._answered = false;
        this._wasParked = false;
        this._info = null;
        this.initiate(ua, session);
    }
    
    /**
     * Setup this call
     * 
     * @param {SipUA} ua
     * @param {SessionAdapter} session
     */ 
    initiate(ua, session) {
        this._info = new CallInfo(this);
        if (this.isIncoming) {
            this.fetchCallDetails();
        } else {
            let parked = this._ua.getParkedCallBySpace(this._info.phoneNumber);
            if (parked !== undefined) {
                this._info = parked.call;
                this._wasParked = true;
            }
        }
        this._session.started = () => {
            this._state = STATE_IN_PROGRESS;
            this._answered = true;
            this._ua.callStarted(this);
            this.fetchCallDetails();
            if (!this._wasParked) {
                eventHub.$emit('call:started', this);
            }
            logger.log('Session has been accepted', {
                number: this.number,
            });
        };
        this._session.ended = () => {
            this._state = STATE_TERMINATED;
            this._ua.callEnded(this);
            eventHub.$emit('call:ended', this);
            logger.log('Session has been ended', {
                number: this.number,
            });
        };
        eventHub.$emit('call:initiated', this);
    }
    
    /**
     * Load call details
     */ 
    fetchCallDetails() {
        if (!this._info.loaded) {
            this._info.load().then((info) => {
                eventHub.$emit('call:details', info);
                logger.log('Remote details have been received', {
                    number: this.number,
                });
            });
        }
    }
    
    /**
     * Load call if this is number is matched
     * 
     * @param {string} number
     */ 
    fetchCallDetailsIfNumberMatches(number) {
        if (!this._info.loaded && this._info.phoneNumber === number) {
            this.fetchCallDetails();
        }
    }
    
    /**
     * Answer this call
     */ 
    answer() {
        if (this._state === STATE_PENDING) {
            this._answered = true;
            this._session.answer();
        }
    }
    
    /**
     * Reject this call
     */ 
    reject() {
        if (this._state === STATE_PENDING) {
            this._session.terminate();
            this._ua.callEnded(this);
        }
    }
    
    /**
     * End this call
     */ 
    terminate() {
        if (this._state === STATE_IN_PROGRESS || this._state === STATE_PENDING) {
            this._session.terminate();
            this._ua.callEnded(this);
        }
    }
    
    /**
     * Mute this call
     */ 
    mute() {
        
    }
    
    /**
     * Unmute this call
     */ 
    unmute() {
        
    }
    
    /**
     * Park this call
     * 
     * @returns {Promise}
     */ 
    park() {
        if (this._state === STATE_IN_PROGRESS) {
            this._state = STATE_PARKING;
            return this.holdCaller(this._info).then((parked) => {
                this._state = STATE_PARKED;
                this._session.terminate();
                logger.log('Remote has been put on hold', {
                    number: this.number,
                });
                return Promise.resolve(parked);
            }).catch(() => {
                this._state = STATE_IN_PROGRESS;
                logger.log('Error while trying to put remote on hold', {
                    number: this.number,
                });
                return Promise.reject('Unable to park call');
            });
        } else {
            return Promise.reject('Can not park this call now');
        }
    }
    
    /**
     * Put caller on hold
     * 
     * @param {CallInfo} callInfo
     * 
     * @returns {Promise}
     */ 
    holdCaller(callInfo) {
        return this.request(callInfo.id, 'hold', {
            caller: callInfo.phoneNumber,
        }).then((response) => {
            let parked = new ParkedCall(callInfo, response.data.lot, response.data.space);
            return Promise.resolve(parked);
        });
    }
    
    /**
     * Send post request 
     * 
     * @param {number} id
     * @param {string} action
     * @param {object} data
     * 
     * @returns {Promise}
     */ 
    request(id, action, data = {}) {
        return axios.post(`/api/v1/calls/call-logs/${action}/${id}`, data);
    }
    
    /**
     * Get UA instance
     * 
     * @returns {SipUA}
     */ 
    get ua() {
        return this._ua;
    }
    
    /**
     * Get current call state
     * 
     * @returns {number}
     */ 
    get state() {
        return this._state;
    }
    
    /**
     * Get is call in pending state
     * 
     * @returns {bool}
     */ 
    get pending() {
        return this._state === STATE_PENDING;
    }
    
    /**
     * Get is call in progress
     * 
     * @returns {bool}
     */ 
    get progress() {
        return this._state === STATE_IN_PROGRESS;
    }
    
    /**
     * Get is call terminated
     * 
     * @returns {bool}
     */ 
    get terminated() {
        return this._state === STATE_TERMINATED;
    }
    
    /**
     * Check if it's outgoing call
     * 
     * @returns {bool}
     */
    get isOutgoing() {
        return this._session.isLocal;
    }
    
    /**
     * Check if it's incoming call
     * 
     * @returns {bool}
     */
    get isIncoming() {
        return this._session.isRemote;
    }
    
    /**
     * Get remote sub info
     * 
     * @returns {SubInfo}
     */ 
    get info() {
        return this._info;
    }
    
    /**
     * Get phone number
     * 
     * @returns {string}
     */ 
    get number() {
        return this._info.phoneNumber;
    }
    
    /**
     * Check if incoming call was answered
     * 
     * @returns {bool}
     */ 
    get answered() {
        return this._answered;
    }
    
    /**
     * Check if incoming call was missed
     * 
     * @returns {bool}
     */ 
    get missed() {
        return this.isIncoming && !this.answered;
    }
    
    /**
     * Check if current call can be parked
     * 
     * @returns {bool}
     */ 
    get canBeParked() {
        return this._state === STATE_IN_PROGRESS
            && this._info.loaded;
    }
    
    /**
     * Check if this call was previously parked
     * 
     * @returns {bool}
     */ 
    get wasParked() {
        return this._wasParked;
    }
    
    /**
     * Get session instance
     * 
     * @returns {SessionAdapter}
     */ 
    get session() {
        return this._session;
    }
}

export {
    Call,
    STATE_PENDING,
    STATE_IN_PROGRESS,
    STATE_TERMINATED,
    STATE_PARKING,
    STATE_PARKED,
};