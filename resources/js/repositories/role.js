import BaseRepository from '@/repositories/base-repository';
import Role from '@/models/role';

class RoleRepository extends BaseRepository
{
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });

        this.endpoint = '/api/v1/roles';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Role(row);
    }
}

export default RoleRepository;
