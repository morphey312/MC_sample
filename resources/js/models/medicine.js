import BaseModel from '@/models/base-model';

/**
 * Medicine model
 */
class Medicine extends BaseModel
{
    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            medicine_uid: '',
            parent_uid: '',
            code: '',
            name: null,
            name_lc1: null,
            name_lc2: null,
            name_lc3: null,
            description_full: '',
            measure: '',
            type: '',
            articul: '',
            store_rests: [],
        }
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            fetch: '/api/v1/medicines/{id}',
        }
    }

    /**
     * Get store list which have medicine rests
     * 
     * @return {string}
     */
    get store_list() {
        if (this.store_rests.length === 0) {
            return '';
        }

        let list = [];
        this.store_rests.forEach(store => list.push(store.description));
        return list.join(', ');
    }

    /**
     * Get localized name
     * 
     * @returns {String}
     */
    get name_i18n() {
        return this.getAttributeI18N('name');
    }
}

export default Medicine;