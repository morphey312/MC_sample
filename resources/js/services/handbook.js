import Loadable from '@/services/loadable';

const SORTED = [
    'city',
    'voip_queue',
];

class Handbook extends Loadable
{
    /**
     * Constructor
     */ 
    constructor() {
        super();
        this._endpoint = '/api/v1/handbook';
    }
    
    /**
     * @inheritdoc
     */ 
    loadComplete(data) {
        this._data = data;
        this.prepareOptions();    
    }

    /**
     * Prepare handbook options
     */
    prepareOptions() {
        this.sortOptions();
    }

    /**
     * Sort all options
     */
    sortOptions() {
        SORTED.forEach((category) => {
            if (this._data[category] !== undefined) {
                this._data[category] = _.sortBy(this._data[category], 'value');
            }
        });
    }

    /**
     * Get options from particular category
     * 
     * @param {string} category
     * 
     * @return {Array|undefined}
     */
    getOptions(category) {
        return this._data[category];
    }
    
    /**
     * Get an option by its key from particular category
     * 
     * @param {string} category
     * @param {string} key
     * 
     * @return {*}
     */
    getOption(category, key) {
        return this._data[category] === undefined
            ? undefined
            : this.findItem(String(key), this._data[category]).value;
    }

    /**
     * Find item in category
     * 
     * @param {string} key 
     * @param {array} items 
     * 
     * @returns {object}
     */
    findItem(key, items) {
        return _.find(items, (item) => String(item.id) === key) || {};
    }
    
    /**
     * Unset option from particular category
     * 
     * @param {string} category
     * @param {string} key
     */
    unsetOption(category, key) {
        if (this._data[category] !== undefined) {
            _.remove(this._data[category], (item) => String(item.id) === String(key));
        }
    }
    
    /**
     * Set option value for particular category
     * 
     * @param {string} category
     * @param {string} key
     * @param {*} value
     */
    setOption(category, key, value) {
        if (this._data[category] !== undefined) {
            let item = _.find(this._data[category], (item) => String(item.id) === String(key));
            if (item !== undefined) {
                item.value = value;
            } else {
                this._data[category].push({
                    id: key,
                    value,
                });
            }
            if (SORTED.indexOf(category) !== -1) {
                this._data[category] = _.sortBy(this._data[category], 'value');
            }
        }
    }
    
    /**
     * Get option key by its value from particular category
     * 
     * @param {string} category
     * @param {string} value
     * 
     * @return {*}
     */
    getOptionKey(category, value) {
        return this._data[category] === undefined
            ? undefined
            : (_.find(this._data[category], (item) => String(item.value) === String(value)) || {}).id;
    }
}

export default new Handbook();