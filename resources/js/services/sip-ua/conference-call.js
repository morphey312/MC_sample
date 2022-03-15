import { 
    Call,
    STATE_PENDING,
    STATE_IN_PROGRESS,
    STATE_TERMINATED,
} from './call';
import eventHub from '@/services/event-hub';

const STATE_BRIDGE_CREATE = 5;

class ConferenceCall extends Call 
{
    /**
     * Constructor
     * 
     * @param {Call} sourceCall
     */
    constructor(sourceCall) {
        super(sourceCall.ua, sourceCall.session);
        this._state = sourceCall.state;
        this._info = sourceCall.info;
        this._conference = null;
        this._participants = [this._info];
    }
    
    /**
     * @inherit
     */ 
    initiate(ua, session) {
        this._answered = true;
        this._session.started = () => {};
        this._session.ended = () => {
            this._state = STATE_TERMINATED;
            this._ua.callEnded(this);
            eventHub.$emit('call:ended', this);
        };
    }
    
    /**
     * Create confbridge for this call
     * 
     * @returns {Promise}
     */ 
    bridge() {
        if (this._state === STATE_IN_PROGRESS) {
            this._state = STATE_BRIDGE_CREATE;
            return this.request(this._info.id, 'bridge').then((response) => {
                this._state = STATE_IN_PROGRESS;
                this._conference = response.data.conference;
                return Promise.resolve(this);
            }).catch(() => {
                this._state = STATE_IN_PROGRESS;
                return Promise.reject('Unable to create bridge');
            });
        } else {
            return Promise.reject('Can not create bridge for this call now');
        }
    }
    
    /**
     * Join parked lot to this call
     * 
     * @param {ParkedCall} parked
     * 
     * @returns {Promise}
     */ 
    join(parked) {
        if (this._state === STATE_IN_PROGRESS) {
            return this.request(this._info.id, 'join', {
                call: parked.call.id,
                caller: parked.number,
            }).then((response) => {
                this._participants.push(parked.call);
                return Promise.resolve(this);
            });
        } else {
            return Promise.reject('Can not join member to this call now');
        }
    }
    
    /**
     * Kick participant of this call from conference
     * 
     * @param {string} number
     * 
     * @returns {Promise}
     */
    kick(number) {
        let info = this.getCallerInfo(number);
        if (info === undefined) {
            return Promise.reject('Specified caller does not participate in this call');
        }
        if (this._state === STATE_IN_PROGRESS) {
            return this.request(this._info.id, 'kick', {
                call: info.id,
                caller: info.phoneNumber,
            }).then((response) => {
                this.forgetParticipant(number);
                return Promise.resolve(this);
            });
        } else {
            return Promise.reject('Can not remove member from this call now');
        }
    }
    
    /**
     * Get member info by number
     * 
     * @param {string} number
     * 
     * @returns {CallInfo}
     */ 
    getCallerInfo(number) {
        return _.find(this._participants, (call) => call.phoneNumber === number);
    }
    
    /**
     * Remove member from the list
     * 
     * @param {string} number
     */ 
    forgetParticipant(number) {
        this._participants = this._participants.filter((call) => call.phoneNumber !== number);
    }
    
    /**
     * Get conference members
     * 
     * @returns {array}
     */ 
    get participants() {
        return this._participants;
    }
    
    /**
     * Get conference number
     * 
     * @returns {string}
     */ 
    get conference() {
        return this._conference;
    }
    
    /**
     * Just to prevent parking
     * 
     * @returns {bool}
     */ 
    get canBeParked() {
        return false;
    }
}

export {
    STATE_BRIDGE_CREATE,
    ConferenceCall,
};