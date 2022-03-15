import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN
} from '@/services/validation';

class Handbook extends BaseModel 
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Handbook';
    }
    
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            value: null,
            value_lc1: null,
            value_lc2: null,
            value_lc3: null,
            key: null,
            category: null,
        };
    }
    
    /** 
     * @inheritdoc
     */
    validation() {
        return {
            value: required.and(maxlen(STRING_MAX_LEN)),
        };
    }
    
    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: `/api/v1/handbook/${this.category}`,
            update: `/api/v1/handbook/${this.category}/{id}`,
            delete: `/api/v1/handbook/${this.category}/{id}`,
        };
    }

    /**
     * Persists data to the database/API.
     *
     * @param {deleteKeyAttribute}  Delete key from request
     * @param {options}             Save options
     * @param {options.method}      Save HTTP method
     * @param {options.url}         Save URL
     * @param {options.data}        Save data
     * @param {options.params}      Query params
     * @param {options.headers}     Query headers
     *
     * @returns {Promise}
     */
    save(deleteKeyAttribute = false, options = {}) {
        let data = this.getSaveData();

        if(deleteKeyAttribute) {
            delete data.key;
        }

        let config = () => {
            return {
                url     : this.getSaveURL(),
                method  : this.getSaveMethod(),
                data    : data,
                params  : this.getSaveQuery(),
                headers : this.getSaveHeaders(),
            }
        };
        
        return this.request(
            config,
            this.onSave,
            this.onSaveSuccess,
            this.onSaveFailure
        );
    }

    /**
     * Get localized value
     * 
     * @returns {String}
     */
    get value_i18n() {
        return this.getAttributeI18N('value');
    }
}

export default Handbook;