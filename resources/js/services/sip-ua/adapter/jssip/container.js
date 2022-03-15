import JsSIP from 'jssip';

class JsSipContainer
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
            let socket = new JsSIP.WebSocketInterface(this._options.websocketServer);
            this._stack = new JsSIP.UA({
                uri: this._options.publicIdentity,
                password: this._options.password,
                realm: this._options.realm,
                register: true,
                sockets: [socket],
            });
            
            this._stack.on('connected', () => {
                this._connected = true;
                if (this._options.onConnected !== undefined) {
                    this._options.onConnected();
                }
            });
            
            this._stack.on('disconnected', () => {
                this._connected = false;
                if (this._options.onDisconnected !== undefined) {
                    this._options.onDisconnected();
                }
            });
            
            this._stack.on('newRTCSession', (e) => {
                this._activeSession = e.session;
                
                e.session.on('accepted', () => {
                    if (this._options.onCallStarted !== undefined) {
                        this._options.onCallStarted(this.serializeSession(e.session));
                    }
                });
                
                e.session.on('ended', () => {
                    this._activeSession = null;
                    if (this._options.onCallEnded !== undefined) {
                        this._options.onCallEnded(this.serializeSession(e.session));
                    }
                });
                
                e.session.on('failed', () => {
                    this._activeSession = null;
                    if (this._options.onCallFailed !== undefined) {
                        this._options.onCallFailed(this.serializeSession(e.session));
                    }
                });
                
                if (e.originator === 'local') {
                    e.session.connection.addEventListener('addstream', (e) => {
                        this._audio.srcObject = e.stream;
                    });
                }
                
                if (this._options.onNewSession !== undefined) {
                    this._options.onNewSession(this.serializeSession(e.session), e.originator);
                }
            });
            
            this._stack.start();
        }
    }
    
    /**
     * Answer active call
     */ 
    answer() {
        if (this._activeSession !== null) {
            this._activeSession.answer({
                pcConfig: {
                    iceServers: [],
                },
                mediaConstraints: {
                    audio: true,
                    video: false,
                },
            });
            this._activeSession.connection.addEventListener('addstream', (e) => {
                this._audio.srcObject = e.stream;
            });
        }
    }
    
    /**
     * Terminate active call
     */ 
    terminate() {
        if (this._activeSession !== null) {
            this._activeSession.terminate();
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
            this._stack.call(to, {
                mediaConstraints: {
                    audio: true, 
                    video: false 
                },
                pcConfig: {
                    iceServers: [],
                },
            });
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
                uri: session.remote_identity.uri,
            },
        };
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

export default JsSipContainer;