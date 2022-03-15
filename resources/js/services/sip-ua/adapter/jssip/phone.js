import JsSip from 'jssip';
import SessionAdapter from './session';

JsSip.debug.enable('JsSIP:*');

class PhoneAdapter
{
    /**
     * Prone constructor
     * 
     * @param {object} settings
     */ 
    constructor(settings) {
        this._settings = settings;
        this._stack = null;
        this.connected = () => {};
        this.disconnected = () => {};
        this.newSession = () => {};
    }
    
    /**
     * Connect to the server
     */ 
    connect() {
        let socket = new JsSip.WebSocketInterface(this._settings.websocketServer);
        this._stack = new JsSip.UA({
            uri: this._settings.publicIdentity,
            password: this._settings.password,
            realm: this._settings.realm,
            register: true,
            sockets: [socket],
        });
        this._stack.on('connected', () => {
            this.connected();
        });
        this._stack.on('disconnected', () => {
            this.disconnected();
        });
        this._stack.on('newRTCSession', (e) => {
            let session = new SessionAdapter(e.session, e.originator, {
                iceServers: this._settings.iceServers,
            });
            this.newSession(session);
        });
        this._stack.start();
    }
    
    /**
     * Disconnect from the server
     */ 
    disconnect() {
        if (this._stack !== null) {
            this._stack.stop();
        }
    }
    
    /**
     * Make a call
     * 
     * @param {string} to
     */ 
    makeCall(to) {
        this._stack.call(to, {
            mediaConstraints: {
                audio: true, 
                video: false 
            },
            pcConfig: {
                iceServers: this._settings.iceServers,
            },
        });
    }
}

export default PhoneAdapter;