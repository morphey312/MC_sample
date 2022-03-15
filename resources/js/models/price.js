import BaseModel from '@/models/base-model';
import Service from '@/models/service';
import Analysis from '@/models/analysis';
import {
    required,
    numeric,
    gte,
    date,
    requiredArray
} from '@/services/validation';
import CONSTANT from '@/constants';

/**
 * Service model
 */
class Price extends BaseModel
{
    /**
     * @inheritdoc
     */
    constructor(attributes = {}, collection = null, options = {}) {
        super(attributes, collection, options);
        this._recommended_cost = 0;
        this._change_type = null;
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            service_id: null,
            date_from: null,
            date_to: null,
            cost: null,
            self_cost: 0,
            currency: CONSTANT.CURRENCY.UAH,
            set_id: null,
            external_price_id: null,
            clinics: [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            cost: required.and(numeric).and(gte(0)),
            self_cost: required.and(numeric).and(gte(0)),
            date_from: required.and(date),
            set_id: required,
            currency: required,
            clinics: requiredArray,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            createInService: '/api/v1/services/prices',
            createInAnalysis: '/api/v1/analyses/prices',
            fetch: '/api/v1/prices/{id}',
            update: '/api/v1/prices/{id}',
            delete: '/api/v1/prices/{id}',
        }
    }

    /**
     * Create price for service
     *
     * @param {object} service
     *
     * @returns {Promise}
     */
    createFor(service) {
        let url;

        if (service instanceof Service) {
            url = this.getURL(this.getRoute('createInService'), this.getRouteParameters());
        } else if (service instanceof Analysis) {
            url = this.getURL(this.getRoute('createInAnalysis'), this.getRouteParameters());
        } else {
            throw 'Unrecognized service type';
        }

        let config = () => ({
            url,
            method: this.getSaveMethod(),
            data: {...this.getSaveData(), service_id: service.id},
            params: this.getSaveQuery(),
            headers: this.getSaveHeaders(),
        });

        return this.request(
            config,
            this.onSave,
            this.onSaveSuccess,
            this.onSaveFailure
        );
    }
}

export default Price;
