import BaseRepository from '@/repositories/base-repository';
import Workspace from '@/models/workspace';

class WorkSpaceRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/workspaces';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new Workspace(row);
    }

    /**
    * Fetch for resources shedule
    */
    fetchDaySheets(filters) {
        return axios.get(this.buildUrl('fetch_day_sheets', {filters}), true)
                    .then((response) => {
                        return Promise.resolve(response.data);
                    });
    }
}

export default WorkSpaceRepository;
