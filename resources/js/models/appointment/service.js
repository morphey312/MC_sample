import BaseModel from '@/models/base-model';
import CONSTANTS from '@/constants';
import {getServicePrice} from '@/services/appointment/service-price';

/**
 * AppointmentService model
 */
class Service extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: '',
            appointment_id: null,
            service_id: null,
            clinic_id: null,
            card_specialization_id: null,
            patient_id: null,
            quantity: 1,
            cost: 0,
            discount: 0,
            is_base: false,
            price: 0,
            items: [],
            payment_destination_id: null,
            container_type: null,
            card_assignment_id: null,
            treatment_assignment_id: null,
            expected_payment: 0,
            issued: false,
            not_debt: false,
            by_policy: false,
            franchise: 0,
            warranter: '',
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            is_base: value => (value > 0) ? true : false,
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/appointments/services',
            fetch: '/api/v1/appointments/services/{id}',
            update: '/api/v1/appointments/services/{id}',
            updateServiceDebt: '/api/v1/appointments/services/{id}/updateServiceDebt',
            isDeleteable: '/api/v1/appointments/services/{id}/isDeleteable'
        }
    }

    /**
     * change service debt status
     *
     * @returns {Promise}
     */
    updateServiceDebt(attributes) {
        let route  = this.getRoute('updateServiceDebt');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let method = this.getUpdateMethod();
        let data   = attributes;

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Cast service attributes to entity
     *
     * @param {object} data
     * @param {object} filters
     */
    castServiceDataToEntity(data, filters, setType = null) {
        let cost = this.getCost(data, filters, (setType || CONSTANTS.PRICE.SET_TYPE.BASE));
        this.set('service_id', data.id);
        this.set('name', data.name);
        this.set('value', data.name);
        this.set('is_base', data.is_base);
        this.set('card_assignment_id', data.card_assignment_id);
        this.set('price', cost.price);
        this.set('price_id', cost.id);
        this.set('specialization', data.specialization);
        this.set('cost', cost.price);
        this.set('payment_destination_id', data.payment_destination_id);
        this.set('self_cost', cost.selfCost);
        this.set('prices', this.getActualPrices(data, filters));
    }

    /**
     * Get service result actual cost
     *
     * @param {object} data
     * @param {object} filters
     *
     * @returns {float}
     */
    getCost(data, filters, setType) {
        return getServicePrice(data, filters, setType);
    }

    /**
     * Get service actual prices
     *
     * @param {object} data
     * @param {object} filters
     *
     * @returns {float}
     */
    getActualPrices(data, filters) {
        let date = `${filters.hasPrice.from}`;
        return data.prices.filter(item => {
            if (item.clinics) {
                if (item.clinics.indexOf(filters.hasPrice.clinic) !== -1 &&
                    item.date_from <= date &&
                    (item.date_to >= date || _.isNil(item.date_to)) &&
                    item.cost > 0) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Get assigner attribute
     *
     * @returns mixed
     */
    get assigner() {
        if (_.isVoid(this.assigner_id)) {
            if (this.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.MEDICINES) {
                let item = this.items.find(item => (item.service && _.isFilled(item.service.assigner_id)));
                return item ? item.service.assigner_id : null;
            }
        }
        return this.assigner_id;
    }

    /**
     * Verify model can be deleted from appointment
     *
     * @returns Promise
     */
    isDeleteable() {
        let method = this.getOption('methods.fetch');
        let route  = this.getRoute('isDeleteable');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data;

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response);
        });
    }

    /**
     * Get detailed service name
     *
     * @returns {string}
     */
    get service_name_detailed() {
        if (this.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES
            && this.attributes.analysis_names && this.attributes.analysis_names.length != 0) {
            return this.attributes.analysis_names.join(', ');
        }
        return this.attributes.service_name_ua;
    }
}

export default Service;
