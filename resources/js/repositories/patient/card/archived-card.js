import BaseRepository from '@/repositories/base-repository';
import ArchivedCard from "@/models/patient/card/archived-card";

class ArchivedCardRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/patients/cards/archived';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new ArchivedCard(row);
    }
}

export default ArchivedCardRepository;
