import BaseRepository from '@/repositories/base-repository';
import Room from '@/models/chat/room';
import CONSTANTS from '@/constants';

class RoomRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{field: 'name', direction: 'asc'}],
            ...options,
        });
        this.endpoint = '/api/v1/chat/room';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Room(row);
    }
}

export default RoomRepository;
