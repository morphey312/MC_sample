import BaseRepository from '@/repositories/base-repository';
import Room from '@/models/department/room';

class RoomRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/departments/rooms';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new Room(row);
    }

    /**
     * Get schedule list
     * 
     * @param {*} filters 
     * @param {*} sort 
     * @param {*} limit 
     * 
     * @returns {Promise}
     */
    fetchSchedule(filters = null, sort = null) {
        let url = this.buildUrl('schedule', {
            ...this.getFilters(filters), 
            ...this.getSort(sort),
        });
        
        let result = this.fetchListInternal(url);
        
        return result.then((data) => {
            return Promise.resolve(data.map(row => this.transformRow(row)));
        });
    }
}

export default RoomRepository;