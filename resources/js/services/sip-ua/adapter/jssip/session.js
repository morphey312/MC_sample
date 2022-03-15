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
        this._session.on('accepted', (e) => {
            if (e.originator !== 'local') {
                this.started();
            }
        });
        this._session.on('ended', (e) => {
            this.ended();
        });
        this._session.on('failed', (e) => {
            this.ended();
        });
    }
    
    /**
     * Answer the call
     */ 
    answer() {
        this._session.answer({
            pcConfig: {
                iceServers: this._settings.iceServers,
            },
            mediaConstraints: {
                audio: true,
                video: false,
            },
        });
        this.started();
    }
    
    /**
     * Terminate the call
     */ 
    terminate() {
        this._session.terminate();
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
        let uri = session.remote_identity.uri;
        return `${uri.scheme}:${uri.user}@${uri.host}`;
    }
}

export default SessionAdapter;