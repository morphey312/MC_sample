import store from '@/store';
import eventHub from '@/services/event-hub';
import { Call } from './sip-ua/call';
import { ConferenceCall } from './sip-ua/conference-call';
import SoundFX from './sip-ua/sfx';
import SipmlPhoneAdapter from './sip-ua/adapter/sipml5/phone';
import ContainerPhoneAdapter from './sip-ua/adapter/container/phone';
import logger from '@/services/logging';

const PhoneAdapter = localStorage.getItem('use_voip_container') === 'true'
    ? ContainerPhoneAdapter
    : SipmlPhoneAdapter;

import {
    StateManager,
    STATE_OFFLINE,
    STATE_ONLINE,
    STATE_BUSY,
    STATE_WRAP_UP,
    STATE_AWAY,
    STATE_CONFERENCE
} from './sip-ua/state-manager';

const STATE_DISCONNECTED = 0;
const STATE_CONNECTED = 1;
const STATE_CONNECTING = 2;
const STATE_DISCONNECTING = 3;

class SipUA
{
    /**
     * Constructor
     */ 
    constructor() {
        this.clearState();
    }
    
    clearState() {
        this._phone = null;
        this._connectionState = STATE_DISCONNECTED;
        this._loading = null;
        this._call = null;
        this._recentCall = null;
        this._sxf = new SoundFX();
        this._stateManager = null;
        this._loadError = false;
        this._defaultRealm = null;
        this._localIdentity = null;
        this._number = null;
        this._parkedCalls = [];
        this._queues = {};
    }
    
    /**
     * Init user agent for current user
     * 
     * @returns {Promise}
     */ 
    init() {
        if (this._phone === null) {
            if (this._loading) {
                return this._loading;
            }
            return this._loading = axios.get('/api/v1/employees/webrtc/settings').then((response) => {
                let account = response.data;
                let wss = account.options.web_socket_server;
                let iceServers = this.makeIceServers(account.options.ice_servers);
                this._defaultRealm = wss.domain;
                this._localIdentity = `sip:${account.sip_number}@${wss.domain}`;
                this._number = account.sip_number;
                this._phone = new PhoneAdapter({
                    realm: `sip:${account.sip_number}@${wss.domain}`,
                    privateIdentity: account.sip_number,
                    publicIdentity: this._localIdentity,
                    password: account.sip_password,
                    websocketServer: `${wss.scheme}${wss.domain}:${wss.port}${wss.path}`,
                    iceServers: iceServers,
                });
                this._phone.connected = () => {
                    this._connectionState = STATE_CONNECTED;
                };
                this._phone.disconnected = () => {
                    this.muteSfx();
                    this._connectionState = STATE_DISCONNECTED;
                };
                this._phone.newSession = (session) => {
                    if (this.available || session.isLocal) {
                        this._call = new Call(this, session);
                        if (this._call.isIncoming) {
                            this.startRing();
                            logger.log('New session has been initiated by remote', {
                                number: this._call.number,
                            });
                        } else if (this._call.wasParked) {
                            this.forgetParkedCall(this._call.info.phoneNumber);
                            logger.log('New session has been initiated from parking call', {
                                number: this._call.number,
                            });
                        } else {
                            this.startRingback();
                            logger.log('New session has been initiated by local', {
                                number: this._call.number,
                            });
                        }
                    } else {
                        session.terminate();
                        logger.log('Session has been discarded, operator is not avaliable', {
                            remote: session.remote_identity,
                        });
                    }
                };
                this.initWatchers();
                return this.requestQueueStatus().then(() => {
                    this._loading = null;
                    return Promise.resolve(this);
                });
            }).catch((e) => {
                this._loadError = true;
            });
        }
        return Promise.resolve(this);
    }
    
    /**
     * Make ICE Servers config
     * 
     * @param {array} options
     * 
     * @returns {array}
     */ 
    makeIceServers(options) {
        let fineOptions = options.filter(o => o.urls !== null);
        return fineOptions;
    }
    
    /**
     * Init watchers
     */ 
    initWatchers() {
        eventHub.$off('broadcast.caller_hangup');
        eventHub.$off('broadcast.caller_resolved');
        eventHub.$off('broadcast.member_pause');
        eventHub.$on('broadcast.caller_hangup', (data) => {
            this.callerDisconnected(data.caller);
        });
        eventHub.$on('broadcast.caller_resolved', (data) => {
            if (this._call !== null) {
                this._call.fetchCallDetailsIfNumberMatches(data.caller);
            }
        });
        eventHub.$on('broadcast.member_pause', (data) => {
            if (data.member === this._number) {
                this._queues[data.queue] = data.paused == 0;
            }
        });
    }
    
    /**
     * Start ringing
     */ 
    startRing() {
        this._sxf.ring();
    }
    
    /**
     * Start ringback
     */ 
    startRingback() {
        this._sxf.ringback();
    }
    
    /**
     * Stop ringing
     */ 
    muteSfx() {
        this._sxf.mute();
    }
    
    /**
     * Called when call started
     * 
     * @param {Call} call
     */
    callStarted(call) {
        this.muteSfx();
        this.stateManager.transit(STATE_BUSY);
    }
    
    /**
     * Called when call ended
     * 
     * @param {Call} call
     */
    callEnded(call) {
        this.muteSfx();
        if (this._call !== null) {
            this._call = null;
            this._recentCall = call;
            switch (this.stateManager.state.name) {
                case STATE_BUSY:
                case STATE_CONFERENCE:
                    this.stateManager.transit(STATE_WRAP_UP);
                    break;
                case STATE_ONLINE:
                    if (call.isOutgoing) {
                        this.stateManager.transit(STATE_WRAP_UP);
                    }
                    break;
            }
        }
    }
    
    /**
     * Caller disconnected
     * 
     * @param {string} number
     */ 
    callerDisconnected(number) {
        if (this.isParked(number)) {
            let call = this.getParkedCall(number);
            this.forgetParkedCall(number);
            eventHub.$emit('parkedcall:gone', call);
        } else if (this._call !== null && (this._call instanceof ConferenceCall)) {
            this._call.forgetParticipant(number);
        }
    }
    
    /**
     * Connect to the server
     */ 
    connect() {
        if (this._connectionState === STATE_DISCONNECTED) {
            if (this._phone === null) {
                if (this._loading) {
                    this._loading.then(() => {
                        if (!this._loadError) {
                            this.connect();
                        }
                    });
                } else {
                    throw 'SipUA.init() should be called first';
                }
            } else if (!this._loadError) {
                this._connectionState = STATE_CONNECTING;
                this._phone.connect();
            }
        }
    }
    
    /**
     * Disconnect from the server
     */ 
    disconnect() {
        if (this._connectionState === STATE_CONNECTED) {
            this._connectionState = STATE_DISCONNECTING;
            this._phone.disconnect();
            this.muteSfx();
        }
    }
    
    /**
     * Destroy the UA
     */ 
    destroy() {
        this.disconnect();
        this.clearState();
    }
    
    /**
     * Initiate a call
     * 
     * @param {string} to
     */ 
    makeCall(to) {
        if (this._connectionState === STATE_CONNECTED) {
            if (this.stateManager.transit(STATE_BUSY)) {
                this.waitUntilPaused().then(() => {
                    this._phone.makeCall(this.qualifyNumber(to));
                    logger.log('Starting new call', {
                        number: to,
                    });
                });
            }
        } else {
            throw 'Unable to make a call while disconnected';
        }
    }

    /**
     * Wait until agent is paused on all queues
     * 
     * @returns {Promise}
     */
    waitUntilPaused() {
        return new Promise((resolve) => {
            _.waitUntil(() => this.isPaused, 100, 5000)
                .then(resolve)
                .catch(() => {
                    console.log('Time of waiting for pause is out');
                    resolve();
                })
        });
    }
    
    /**
     * Extend sip number to fully qualified format
     * 
     * @param {string} number
     * 
     * @returns {string}
     */ 
    qualifyNumber(number) {
        let qualified = number;
        if (qualified.substring(0, 4) !== 'sip:') {
            qualified = 'sip:' + qualified;
        }
        if (qualified.indexOf('@') === -1) {
            qualified = qualified + '@' + this._defaultRealm;
        }
        return qualified;
    }
    
    /**
     * Answer the active call
     */ 
    answer() {
        if (this._call !== null) {
            this._call.answer();
        }
    }
    
    /**
     * Reject the active call
     */ 
    reject() {
        if (this._call !== null) {
            this._call.reject();
        }
    }
    
    /**
     * Terminate the active call
     */ 
    terminate() {
        if (this._call !== null) {
            this._call.terminate();
        }
    }
    
    /**
     * Park active call
     * 
     * @returns {Promise}
     */ 
    park() {
        if (this._call !== null && this._call.canBeParked) {
            return this._call.park().then((parked) => {
                this._parkedCalls.push(parked);
            });
        } else {
            return Promise.reject('Can not park call at this moment');
        }
    }
    
    /**
     * Withdraw parked call
     * 
     * @param {ParkedCall} call
     */ 
    withdrawParked(call) {
        if (this._parkedCalls.indexOf(call) !== -1) {
            this.makeCall(call.space);
        }
    }
    
    /**
     * Check if call is parked
     * 
     * @param {string} number
     * 
     * @returns {bool}
     */ 
    isParked(number) {
        return this._parkedCalls.some((call) => call.number === number);
    }
    
    /**
     * Get parked call by its number
     * 
     * @returns {ParkedCall}
     */ 
    getParkedCall(number) {
        return _.find(this._parkedCalls, (call) => call.number === number);
    }
    
    /**
     * Get parked call by lot space
     * 
     * @returns {ParkedCall}
     */
    getParkedCallBySpace(space) {
        return _.find(this._parkedCalls, (call) => call.space === space);
    }
    
    /**
     * Remove call from parked calls
     * 
     * @param {string} number
     */ 
    forgetParkedCall(number) {
        this._parkedCalls = this._parkedCalls.filter((call) => call.number !== number);
    }
    
    /**
     * Start conference
     * 
     * @returns {Promise}
     */ 
    startConference() {
        if (this._call !== null && !(this._call instanceof ConferenceCall)) {
            let original = this._call;
            this._call = new ConferenceCall(original);
            return this._call.bridge().then(() => {
                this.stateManager.transit(STATE_CONFERENCE);
                logger.log('Conference has been started', {
                    number: this._call.number,
                });
            }).catch(() => {
                this._call = original;
                logger.log('Error while trying to start conference', {
                    number: this._call.number,
                });
            });
        } else {
            return Promise.reject('Can not start conference at this moment');
        }
    }
    
    /**
     * Join parked member to conference
     * 
     * @param {ParkedCall} parked
     * 
     * @returns {Promise}
     */ 
    joinConference(parked) {
        if (this._call !== null && (this._call instanceof ConferenceCall)) {
            return this._call.join(parked).then(() => {
                this.forgetParkedCall(parked.number);
                logger.log('Remote has been joined conference', {
                    number: this._call.number,
                });
            }).catch((e) => {
                logger.log('Error while trying to join conference', {
                    number: this._call.number,
                });
                return Promise.reject(e);
            });
        } else {
            return Promise.reject('Can not join member');
        }
    }
    
    /**
     * Remove participant from conference 
     * 
     * @param {string} number
     * 
     * @returns {Promise}
     */ 
    leaveConference(number) {
        if (this._call !== null && (this._call instanceof ConferenceCall)) {
            return this._call.kick(number).then((res) => {
                logger.log('Remote left conference', {
                    number: this._call.number,
                });
                return res;
            }).catch((e) => {
                logger.log('Error while trying to leave conference', {
                    number: this._call.number,
                });
                return Promise.reject(e);
            });
        } else {
            return Promise.reject('Can not kick member');
        }
    }

    /**
     * Request agent queue status update
     */
    requestQueueStatus() {
        return axios.get('/api/v1/employees/webrtc/queues-status');
    }
    
    /**
     * Check if state can be changed to the given one
     * 
     * @param {string} state
     * 
     * @returns {bool}
     */ 
    canTransit(state) {
        return this.stateManager.canTransit(state);
    }
    
    /**
     * Start session
     */ 
    startSession() {
        this.stateManager.transit(STATE_ONLINE);
    }
    
    /**
     * End session
     */ 
    endSession() {
        this.stateManager.transit(STATE_OFFLINE);
    }
    
    /**
     * Get connection state
     * 
     * @returns {number}
     */ 
    get connectionState() {
        return this._connectionState;
    }
    
    /**
     * Get is UA in connected state
     * 
     * @returns {bool}
     */ 
    get connected() {
        return this._connectionState === STATE_CONNECTED;
    }
    
    /**
     * Get is UA in disconnected state
     * 
     * @returns {bool}
     */ 
    get disconnected() {
        return this._connectionState === STATE_DISCONNECTED;
    }
    
    /**
     * Get is UA is now connecting
     * 
     * @returns {bool}
     */ 
    get connecting() {
        return this._connectionState === STATE_CONNECTING;
    }
    
    /**
     * Get is UA is now disconnecting
     * 
     * @returns {bool}
     */ 
    get disconnecting() {
        return this._connectionState === STATE_DISCONNECTING;
    }
    
    /**
     * Get active call
     * 
     * @returns {Call}
     */ 
    get call() {
        return this._call;
    }
    
    /**
     * Get recent call
     * 
     * @returns {Call}
     */ 
    get recentCall() {
        return this._recentCall;
    }
    
    /**
     * Get state manager
     * 
     * @returns {StateManager}
     */ 
    get stateManager() {
        if (this._stateManager === null) {
            this._stateManager = new StateManager(this);
        }
        return this._stateManager;
    }
    
    /**
     * Get current state name
     * 
     * @returns {string}
     */ 
    get state() {
        return this.stateManager.state.name;
    }
    
    /**
     * Check if state is available
     * 
     * @returns {bool}
     */ 
    get available() {
        return this.stateManager.state.name === STATE_ONLINE;
    }
    
    /**
     * Set availability status
     * 
     * @param {bool} available
     * 
     * @returns {bool}
     */ 
    set available(val) {
        if (val) {
            this.stateManager.transit(STATE_ONLINE);
            return true;
        } else {
            this.stateManager.transit(STATE_AWAY);
            return false;
        }
    }
    
    /**
     * Get local identity
     * 
     * @returns {string}
     */ 
    get localIdentity() {
        return this._localIdentity;
    }
    
    /**
     * Get phone number
     * 
     * @returns {string}
     */ 
    get number() {
        return this._number;
    }
    
    /**
     * Get list of parked calls
     * 
     * @returns {array}
     */ 
    get parkedCalls() {
        return this._parkedCalls;
    }

    /**
     * Get list of active queues
     * 
     * @returns {array}
     */
    get activeQueues() {
        return Object.keys(this._queues).filter(queue => this._queues[queue]);
    }

    /**
     * Get list of paused queues
     * 
     * @returns {array}
     */
    get pausedQueues() {
        return Object.keys(this._queues).filter(queue => !this._queues[queue]);
    }

    /**
     * Check if agent is paused on all queues
     * 
     * @returns {bool}
     */
    get isPaused() {
        return Object.keys(this._queues).every(queue => !this._queues[queue]);
    }
}

const UA = new SipUA();

export {
    UA,
    STATE_DISCONNECTED,
    STATE_CONNECTED,
    STATE_CONNECTING,
    STATE_DISCONNECTING,
};