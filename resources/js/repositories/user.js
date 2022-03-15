import BaseRepository from '@/repositories/base-repository';
import User from '@/models/user';

class UserRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/users';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new User(row);
    }
}

export default UserRepository;