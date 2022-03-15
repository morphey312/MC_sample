import BaseModel from '@/models/base-model';

import {
    required,
} from '@/services/validation';

class CardSpecialization extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            clinic_id: null,
            patient_id: null,
            specialization_id: null,
        }
    }
    
    /**
     * @inheritdoc
     */
    validation() {
        return {
            clinic_id: required,
            specialization_id: required,
        };
    }
    
    /** 
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/specializations',
            delete: '/api/v1/patients/cards/specializations/{id}',
            isDeleteable: '/api/v1/patients/cards/specializations/{id}/isDeleteable'
        };
    }

    /**
     * @inheritdoc
     */
    options() {
        return {
            methods: {
                isDeleteable: 'GET',
            }
        }
    }

    /**
     * Get related models
     *
     * @returns Promise
     */
    isDeleteable() {
        let method = this.getOption('methods.isDeleteable');
        let route  = this.getRoute('isDeleteable');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data;

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response);
        });
    }
    
    /**
     * Get card number
     * 
     * @returns {string}
     */ 
    get number() {
        return `${(this._parent && this._parent.number) ? this._parent.number : ''} ${this.specialization ? this.specialization.short_name : ''}`;
    }
    
    /**
     * Get card clinic
     * 
     * @returns {string}
     */ 
    get clinic_name() {
        return this._parent.clinic_name;
    }
}

export default CardSpecialization;