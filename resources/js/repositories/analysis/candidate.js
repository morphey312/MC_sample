import BaseRepository from '@/repositories/base-repository';
import AnalysesCandidate from '@/models/analysis/candidate';

class CandidateRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'name', direction: 'asc'}],
            ...options
        });
        this.endpoint = '/api/v1/analyses/candidates';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new AnalysesCandidate(row);
    }
}

export default CandidateRepository;
