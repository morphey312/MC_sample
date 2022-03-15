import BaseRepository from '@/repositories/base-repository';
import Result from '@/models/analysis/result';

class ResultRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/analyses/results';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Result(row);
    }

    /**
     * Send results to patients
     * 
     * @param {array} resultIds
     */
    sendResults(resultIds) {
        return axios.post(this.buildUrl('submit'), {
            id: resultIds,
        }).then((response) => {
            return response.data;
        });
    }
}

export default ResultRepository;