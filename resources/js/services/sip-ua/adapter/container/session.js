class SessionAdapter
{
    /**
     * Constructor
     * 
     * @param {object} session
     * @param {string} originator
     * @param {BroadcastChannel} channel
     */ 
    constructor(session, originator, channel) {
        this._channel = channel;
        this._session = session;
        this._originator = originator;
        this._remoteUri = this.getRemoteUri(session);
        this.started = () => {};
        this.ended = () => {};
    }
    
    /**
     * Answer the call
     */ 
    answer() {
        this._channel.postMessage({message: 'answer'});
    }
    
    /**
     * Terminate the call
     */ 
    terminate() {
        this._channel.postMessage({message: 'terminate'});
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
        return session.remote_identity.uri;
    }
    
    /**
     * Trigger call accepted callback
     */ 
    triggerAccepted() {
        this.started();
    }
    
    /**
     * Trigger call ended callback
     */ 
    triggerEnded() {
        this.ended();
    }
}

export default SessionAdapter;