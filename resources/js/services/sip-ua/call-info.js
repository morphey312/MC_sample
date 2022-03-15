import Loadable from '@/services/loadable';
import CallLog from '@/models/calls/call-log';

const URI_RE = new RegExp('[<]?sip[:]([0-9+]+)@[^>]+[>]?');
const SIP_RE = new RegExp('^[0-9]{3,5}$');

const DIR_INCOMING = 'incoming';
const DIR_OUTGOING = 'outgoing';

class CallInfo extends Loadable
{
    /**
     * Constructor
     * 
     * @param {Call} call
     */ 
    constructor(call) {
        super();
        this._call = call;
        this._number = this.parseNumber(call.session.remote_identity);
        this._endpoint = `/api/v1/calls/call-logs/active/${this._number}?ctx=${call.ua.number}&type=${this.direction}`;
    }
    
    /**
     * @inheritdoc
     */ 
    loadComplete(data) {
        this._data = new CallLog(data);
    }
    
    /**
     * Parse URI and fetch number from it
     * 
     * @param {string} uri
     * 
     * @returns {string}
     */ 
    parseNumber(uri) {
        let match = uri.match(URI_RE);
        if (match !== null) {
            uri = match[1];
        }
        let plus = uri.indexOf('+');
        if (plus !== -1) {
            // remove clinic prefix
            uri = uri.substr(plus + 1);
        }
        if (uri.length === 12 && uri.substr(0, 3) === '380') {
            // Ukraine full format
            return uri.substr(2);
        }
        // Other countries/Ukraine short format
        return uri;
    }

    /**
     * Get call direction
     * 
     * @returns {String}
     */
    get direction() {
        return this._call.isIncoming
            ? DIR_INCOMING
            : DIR_OUTGOING;
    }
    
    /**
     * Get phone number
     * 
     * @returns {string}
     */ 
    get phoneNumber() {
        return this._number;
    }
    
    /**
     * Check if related number is SIP
     * 
     * @returns {bool}
     */ 
    get isSip() {
        return SIP_RE.test(this._number);
    }
    
    /**
     * Check if it's incoming call
     * 
     * @returns {bool}
     */ 
    get isIncoming() {
        return this._call.isIncoming;
    }
    
    /**
     * Check if it's outgoing call
     * 
     * @returns {bool}
     */ 
    get isOutgoing() {
        return this._call.isOutgoing;
    }
    
    /**
     * Get caller
     * 
     * @returns {object}
     */ 
    get caller() {
        return this._data ? this._data.caller : null;
    }
    
    /**
     * Get caller name
     * 
     * @returns {string}
     */ 
    get callerName() {
        return this.caller ? this.caller.name : null;
    }
    
    /**
     * Get callee
     * 
     * @returns {object}
     */ 
    get callee() {
        return this._data ? this._data.callee : null;
    }
    
    /**
     * Get callee name
     * 
     * @returns {string}
     */ 
    get calleeName() {
        return this.callee ? this.callee.name : null;
    }
    
    /**
     * Get subscriber at the other end
     * 
     * @returns {object}
     */ 
    get versa() {
        return this.isIncoming ? this.caller : this.callee;
    }
    
    /**
     * Get name of subscriber at the other end
     * 
     * @returns {string}
     */ 
    get versaName() {
        return this.versa ? this.versa.name : null;
    }
    
    /**
     * Get related clinic
     * 
     * @returns {object}
     */
    get clinic() {
        return this._data ? this._data.clinic : null;
    }
    
    /**
     * Get related patient
     * 
     * @returns {object}
     */ 
    get patient() {
        return this._data ? this._data.patient : null;
    }
    
    /**
     * Get source ofcall
     * 
     * @returns {string}
     */
    get source() {
        return this._data ? this._data.source : null;
    }
    
    /**
     * Get related contacts
     * 
     * @returns {array}
     */ 
    get related_contacts() {
        return this._data ? this._data.related_contacts : [];
    }
    
    /**
     * Get call ID
     * 
     * @returns {string}
     */ 
    get id() {
        return this._data ? this._data.id : null;
    }
}

export default CallInfo;