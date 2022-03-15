class Sipml5Container
{
    /**
     * Constructor
     * 
     * @param {object} options
     */ 
    constructor(options) {
        this._options = options;
        this._stack = null;
        this._connected = false;
        this._activeSession = null;
        this._audio = this.createAudio();
    }
    
    /**
     * Connect to the server and start stack
     */ 
    start() {
        if (this._stack === null) {
            this.init().then(() => {
                this._stack =  new SIPml.Stack({
                    realm: this._options.realm, 
                    impi: this._options.privateIdentity, 
                    impu: this._options.publicIdentity, 
                    password: this._options.password,
                    websocket_proxy_url: this._options.websocketServer,
                    ice_servers: this._options.iceServers,
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
                                    this._stack = null;
                                    this._connected = false;
                                    if (this._options.onDisconnected !== undefined) {
                                        this._options.onDisconnected();
                                    }
                                    break;
                                
                                case 'i_new_call':
                                    this._activeSession = e.newSession;
                                    this.setupSession(e.newSession);
                                    if (this._options.onNewSession !== undefined) {
                                        this._options.onNewSession(this.serializeSession(e.newSession), 'remote');
                                    }
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
    }
    
    /**
     * Init SIPml
     * 
     * @returns {Promise}
     */ 
    init() {
        if (SIPml.isInitialized()) {
            return Promise.resolve(this);
        }
        
        return new Promise((resolve) => {
            SIPml.setDebugLevel('fatal');
            SIPml.init((e) => {
                resolve(this);
            });
        });
    }
    
    /**
     * Register 
     */ 
    register() {
        let session = this._stack.newSession('register', {
            expires: 200,
            events_listener: {
                events: '*', 
                listener: (e) => {
                    switch (e.type) {
                        case 'connected':
                            this._connected = true;
                            if (this._options.onConnected !== undefined) {
                                this._options.onConnected();
                            }
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
        session.register();
    }
    
    /**
     * Setup new session
     * 
     * @param {object} session
     */ 
    setupSession(session) {
        session.setConfiguration({
            audio_remote: this._audio,
            events_listener: {
                events: '*', 
                listener: (e) => {
                    switch (e.type) {
                        case 'terminated':
                            this._activeSession = null;
                            if (this._options.onCallEnded !== undefined) {
                                this._options.onCallEnded(this.serializeSession(session));
                            }
                            break;
                            
                        case 'connected':
                            if (this._options.onCallStarted !== undefined) {
                                this._options.onCallStarted(this.serializeSession(session));
                            }
                            break;
                            
                        default:
                            break;
                    }
                },
            },
            sip_caps: [
                { name: '+g.oma.sip-im' },
            ],
        });
    }
    
    /**
     * Answer active call
     */ 
    answer() {
        if (this._activeSession !== null) {
            this._activeSession.accept();
        }
    }
    
    /**
     * Terminate active call
     */ 
    terminate() {
        if (this._activeSession !== null) {
            this._activeSession.hangup();
            this._activeSession = null;
        }
    }
    
    /**
     * Disconnect from the server
     */ 
    disconnect() {
        if (this._stack !== null) {
            this._stack.stop();
            this._stack = null;
        }
    }
    
    /**
     * Make a call
     * 
     * @param {string} to
     */ 
    call(to) {
        if (this._stack !== null) {
            this._activeSession = this._stack.newSession('call-audio');
            this._activeSession.call(to);
            this.setupSession(this._activeSession);
            if (this._options.onNewSession !== undefined) {
                this._options.onNewSession(this.serializeSession(this._activeSession), 'local');
            }
        }
    }
    
    /**
     * Serialize session into plain object
     * 
     * @param {RTCSession} session
     * 
     * @returns {object}
     */ 
    serializeSession(session) {
        return {
            remote_identity: {
                uri: this.getRemoteUri(session),
            },
        };
    }
    
    /**
     * Get remote URI from session
     * 
     * @returns {string}
     */ 
    getRemoteUri(session) {
        let uri = session.getRemoteUri();
        let match = uri.match(/<(.+)>/);
        if (match) {
            return match[1];
        }
        return uri;
    }
    
    /**
     * Create audio
     * 
     * @return HTMLElement
     */ 
    createAudio() {
        let audio = document.createElement('audio');
        audio.setAttribute('autoplay', 'autoplay');
        return audio;
    }
    
    /**
     * Check if connected to the server
     * 
     * @returns {bool}
     */ 
    get connected() {
        return this._connected;
    }
}

export default Sipml5Container;