import BaseRepository from '@/repositories/base-repository';
import Diagnosis from '@/models/diagnosis';

class DiagnosisRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/diagnoses';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Diagnosis(row);
    }
}

export default DiagnosisRepository;