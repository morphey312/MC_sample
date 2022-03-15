class SessionAdapter
{
    /**
     * Constructor
     * 
     * @param {object} session
     * @param {object} settings
     */ 
    constructor(session, originator, settings) {
        this._settings = settings;
        this._session = session;
        this._originator = originator;
        this._remoteUri = this.getRemoteUri(session);
        this.started = () => {};
        this.ended = () => {};
        this._session.setConfiguration({
            audio_remote: this._settings.audio,
            events_listener: {
                events: '*', 
                listener: (e) => {
                    switch (e.type) {
                        case 'terminated':
                            this.ended();
                            break;
                            
                        case 'connected':
                            this.started();
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
     * Answer the call
     */ 
    answer() {
        this._session.accept();
    }
    
    /**
     * Terminate the call
     */ 
    terminate() {
        this._session.hangup();
    }
    
    /**
     * Get originator
     * 
     * @returns {string}
     */ 
    get originator() {
        return this._originator;
    }
    
    /**
     * Get remote identity
     * 
     * @returns {string}
     */ 
    get remote_identity() {
        return this._remoteUri;
    }
    
    /**
     * Check if session started locally
     * 
     * @returns {bool}
     */ 
    get isLocal() {
        return this._originator === 'local';
    }
    
    /**
     * Check if session started remotely
     * 
     * @returns {bool}
     */ 
    get isRemote() {
        return this._originator === 'remote';
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
            return match[0];
        }
        return uri;
    }
}

export default SessionAdapter;