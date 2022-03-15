import BaseRepository from '@/repositories/base-repository';
import NumberingKind from '@/models/discount-card-type/numbering-kind';

class NumberingKindRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/discount-card-types/numbering-kinds';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new NumberingKind(row);
    }
}

export default NumberingKindRepository;