import BaseRepository from '@/repositories/base-repository';
import Clinic from '@/models/clinic';
import store from '@/store';

let defaultClinic = null;

class ClinicRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super({
            accessLimit: false,
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/clinics';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new Clinic(row);
    }
    
    /** 
     * @inheritdoc
     */
    getFilters(filters) {
        let result = super.getFilters(filters);

        if (this._options.accessLimit) {
            let clinics = store.state.user.workingClinics;
            clinics.length === 0 ? clinics = null : null
            result.filters = {
                id: clinics,
                ...(result.filters || {}),
            };
        }
        
        return result;
    }

    /**
     * Get default clinic
     * 
     * @returns {Promise}
     */
    getDefaultClinic() {
        if (defaultClinic !== null) {
            return Promise.resolve(defaultClinic);
        }
        return this.fetchList({is_default: 1}).then((list) => {
            defaultClinic = list.length === 0 ? false : list[0];
            return defaultClinic;
        });
    }
}

export default ClinicRepository;
