import Loadable from '@/services/loadable';
import eventHub from '@/services/event-hub';

class VoipCounters extends Loadable
{
    /**
     * Constructor
     */ 
    constructor() {
        super();
        this._endpoint = '/api/v1/voip/counters';
        
        eventHub.$on('broadcast.new_personal_task', (data) => {
            this.tasks++;
        });
        
        eventHub.$on('broadcast.new_site_enquiry', (data) => {
            this.enquiries++;
        });

        eventHub.$on('broadcast.wait_list_record_found', (data) => {
            this.wait_list_records++;
        });
        
        eventHub.$on('processLog:completed', (processLog) => {
            if (processLog.enquiry !== null) {
                this.enquiries--;
            }

            if (processLog.watiListRecord !== null) {
                this.wait_list_records--;
            }
        });
        
        eventHub.$on('logout', (data) => {
            this.flush();
        });
    }
    
    /**
     * @inheritdoc
     */ 
    loadComplete(data) {
        this._data = data;
    }
    
    /**
     * Get tasks count
     * 
     * @returns {number}
     */ 
    get tasks() {
        return this._data ? this._data.tasks : 0;
    }
    
    /**
     * Set tasks count
     * 
     * @param {number} value
     * 
     * @returns {number}
     */ 
    set tasks(value) {
        if (this._data && value >= 0) {
            this._data.tasks = value;
        }
    }
    
    /**
     * Get enquiries count
     * 
     * @returns {number}
     */ 
    get enquiries() {
        return this._data ? this._data.enquiries : 0;
    }
    
    /**
     * Set enquiries count
     * 
     * @param {number} value
     * 
     * @returns {number}
     */ 
    set enquiries(value) {
        if (this._data && value >= 0) {
            this._data.enquiries = value;
        }
    }

    /**
     * Get wait_list_records count
     * 
     * @returns {number}
     */ 
    get wait_list_records() {
        return this._data ? this._data.wait_list_records : 0;
    }
    
    /**
     * Set wait_list_records count
     * 
     * @param {number} value
     * 
     * @returns {number}
     */ 
    set wait_list_records(value) {
        if (this._data && value >= 0) {
            this._data.wait_list_records = value;
        }
    }
}

export default new VoipCounters();