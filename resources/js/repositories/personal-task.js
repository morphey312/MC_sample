import BaseRepository from '@/repositories/base-repository';
import PersonalTask from '@/models/personal-task';

class PersonalTaskRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/personal-tasks';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new PersonalTask(row);
    }
}

export default PersonalTaskRepository;