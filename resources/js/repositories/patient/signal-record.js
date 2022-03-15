import BaseRepository from '@/repositories/base-repository';
import SignalRecord from '@/models/patient/signal-record';

class SignalRecordRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/signal-records';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new SignalRecord(row);
    }
    
    /**
     * Get a record related to the patient
     * 
     * @param {number} patient
     * 
     * @returns {Promise}
     */ 
    getPatientRecord(patient) {
        return this.fetch({patient}, null, null, 1, 1).then((result) => {
            return result.rows.length === 0 ? null : result.rows[0];
        });
    }
}

export default SignalRecordRepository;