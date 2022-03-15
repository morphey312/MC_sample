import BaseRepository from '@/repositories/base-repository';
import Permission from '@/models/permission';

class PermissionRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/permissions';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Permission(row);
    }
}

export default PermissionRepository;