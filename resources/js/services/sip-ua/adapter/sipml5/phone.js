import SessionAdapter from './session';

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
        this._session = null;
        this._audio = null;
        this.connected = () => {};
        this.disconnected = () => {};
        this.newSession = () => {};
    }
    
    /**
     * Connect to the server
     */ 
    connect() {
        this.init().then(() => {
            this._stack =  new SIPml.Stack({
                realm: this._settings.realm, 
                impi: this._settings.privateIdentity, 
                impu: this._settings.publicIdentity, 
                password: this._settings.password,
                websocket_proxy_url: this._settings.websocketServer,
                ice_servers: this._settings.iceServers,
                events_listener: { 
                    events: '*', 
                    listener: (e) => {
                        switch (e.type) {
                            case 'started':
                                this.register();
                                break;
                                
                            case 'stopped': 
                            case 'failed_to_start': 
                            case 'failed_to_stop':
                                this._session = null;
                                this._stack = null;
                                this.disconnected();
                                break;
                            
                            case 'i_new_call':
                                let session = new SessionAdapter(e.newSession, 'remote', {
                                    audio: this.audio,
                                });
                                this.newSession(session);
                                break;
                                
                            default:
                                break;
                        }
                    },
                },
            });
            this._stack.start();
        });
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
        let bare = this._stack.newSession('call-audio');
        bare.call(to);
        let session = new SessionAdapter(bare, 'local', {
            audio: this.audio,
        });
        this.newSession(session);
    }
    
    /**
     * Init adapter
     * 
     * @returns {Promise}
     */ 
    init() {
        if (SIPml.isInitialized()) {
            return Promise.resolve(this);
        }
        return new Promise((resolve) => {
            SIPml.setDebugLevel('fatal');
            // SIPml.setDebugLevel('debug');
            SIPml.init((e) => {
                resolve(this);
            });
        });
    }
    
    /**
     * Register 
     */ 
    register() {
        this._session = this._stack.newSession('register', {
            expires: 200,
            events_listener: {
                events: '*', 
                listener: (e) => {
                    switch (e.type) {
                        case 'connected':
                            this.connected();
                            break;
                        
                        default:
                            break;
                    }
                },
            },
            sip_caps: [
                { name: '+g.oma.sip-im', value: null },
                { name: '+audio', value: null },
            ]
        });
        this._session.register();
    }
    
    /**
     * Get audio element
     * 
     * @returns {object}
     */ 
    get audio() {
        if (this._audio === null) {
            this._audio = document.createElement('audio');
            this._audio.setAttribute('autoplay', 'autoplay');
        }
        return this._audio;
    }
}

export default PhoneAdapter;