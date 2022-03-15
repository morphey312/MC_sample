import BaseRepository from '@/repositories/base-repository';
import Icon from '@/models/discount-card-type/icon';

class IconRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/discount-card-types/icons';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Icon(row);
    }
}

export default IconRepository;