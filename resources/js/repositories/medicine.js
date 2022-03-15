import BaseRepository from '@/repositories/base-repository';
import Medicine from '@/models/medicine';
import AssignedMedicine from '@/models/patient/assigned-medicine';

class MedicineRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/medicines';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new Medicine(row);
    }

    /**
     * Fetch list 
     * 
     * @param {object} filters 
     * @param {int} page 
     * @param {int} limit 
     * 
     * @return {Promise}
     */
    fetchForAssign(filters = null, sort = null, page = 1, limit = 50) {
        return axios.get(this.buildUrl(null, {
            ...this.getFilters(filters),
            ...this.getSort(sort),
            page, 
            limit, 
            }))
            .then((response) => {
                let medicine = new AssignedMedicine();
                let rows = response.data.data.map((row) => medicine.createAssignedMedicine(row, filters));
                return {rows};
            });
    }
}

export default MedicineRepository;