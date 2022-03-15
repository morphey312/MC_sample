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
        this._session = null;
        this._channel = new BroadcastChannel('voip_channel');
        this.connected = () => {};
        this.disconnected = () => {};
        this.newSession = () => {};
        this._channel.onmessage = (event) => {
            let args = event.data;
            switch (args.message) {
                case 'connected':
                    this.connected();
                    break;
                case 'disconnected':
                    this.disconnected();
                    break;
                case 'gone':
                    this.disconnected();
                    break;
                case 'session':
                    this.createSession(args.session, args.originator);
                    break;
                case 'accepted':
                    this.acceptSession();
                    break;
                case 'ended':
                    this.clearSession();
                    break;
                case 'failed':
                    this.clearSession();
                    break;
            }
        }
    }
    
    /**
     * Connect to the server
     */ 
    connect() {
        this.createContainer().then(() => {
            this._channel.postMessage({message: 'start', args: this._settings});
        });
    }
    
    /**
     * Disconnect from the server
     */ 
    disconnect() {
        this._channel.postMessage({message: 'stop'});
    }
    
    /**
     * Make a call
     * 
     * @param {string} to
     */ 
    makeCall(to) {
        this._channel.postMessage({message: 'call', args: to});
    }
    
    /**
     * Create session adapter and handover to client
     * 
     * @param {object} session
     * @param {string} originator
     */ 
    createSession(session, originator) {
        this._session = new SessionAdapter(session, originator, this._channel);
        this.newSession(this._session);
    }
    
    /**
     * Accept active session
     */ 
    acceptSession() {
        if (this._session !== null) {
            this._session.triggerAccepted();
        }
    }
    
    /**
     * Clear active session
     */ 
    clearSession() {
        if (this._session !== null) {
            this._session.triggerEnded();
            this._session = null;
        }
    }
    
    /**
     * Create container
     * 
     * @returns {Promise}
     */ 
    createContainer() {
        if (localStorage.getItem('voip_container') === 'true') {
            return Promise.resolve(true);
        }
        return new Promise((resolve) => {
            window.open('/voip-container', 'voip_container', 'innerWidth=300,innerHeight=150,status=no,menubar=no,toolbar=no,location=no,resizable=no');
            let listener = (e) => {
                if (e.key === 'voip_container' && e.newValue === 'true') {
                    window.removeEventListener('storage', listener);
                    resolve(true);
                }
            }
            window.addEventListener('storage', listener);
        });
    }
}

export default PhoneAdapter;