import BaseRepository from '@/repositories/base-repository';
import EhealthReason from '@/models/ehealth/encounter/hand-book/reason';

class EhealthReasonRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/ehealth/encounter/handbook/reasons';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new EhealthReason(row);
    }
}

export default EhealthReasonRepository;
