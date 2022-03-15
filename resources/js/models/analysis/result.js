import BaseModel from '@/models/base-model';
import {dateFormat} from '@/services/format';
import CONSTANTS from '@/constants';

/**
 * Result model
 */
class Result extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            analysis_id: null,
            patient_id: null,
            assigner_id: null,
            card_specialization_id: null,
            card_assignment_id: null,
            appointment_id: null,
            status: null,
            payed: 0,
            clinic_id: null,
            quantity: 1,
            cost: 0,
            price_id: null,
            discount: null,
            custom_name: null,
            verification_code: null,
            analysis: {
                name: null,
                laboratory_code: null,
                clinic: {},
                price: 0,
            },
            appointment: null,
            date_expected_pass: null,
            date_pass: null,
            date_expected_ready: null,
            date_ready: null,
            date_sent_email: null,
            created_at: null,
            attachments: [],
            blank_id: null,
            blank_data: null,
            by_policy: false,
            franchise: 0,
            warranter: '',
            is_outclinic: false,
            delivery_status: null,
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            cost: (value) => {
                let price = value;

                if (this.isNew() && !_.isNil(this.analysis) && this.analysis.price) {
                    price = this.analysis.price;
                }
                return Number(price).toFixed(2);
            },
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/analyses/results',
            fetch: '/api/v1/analyses/results/{id}',
            update: '/api/v1/analyses/results/{id}',
            delete: '/api/v1/analyses/results/{id}',
            printed: '/api/v1/analyses/results/printed/{id}',
            downloaded: '/api/v1/analyses/results/downloaded/{id}',
        }
    }

    /**
     * @inheritdoc
     */
    options() {
        return {
            methods: {
                printed: 'POST',
                downloaded: 'POST',
            }
        }
    }

    /**
     * Cast analysis attributes to entity
     *
     * @param {object} data
     * @param {object} filters
     */
    castAnalysisDataToEntity(data, filters) {
        let cost = this.getAnalysesCost(data, filters);
        let price = cost.price.toFixed(2);
        data.analysis.price = price;
        this.set('price_id', cost.id);
        this.set('analysis', data.analysis);
        this.set('analysis_id', data.analysis_id);
        this.set('cost', price);
        this.set('prices', this.getActualPrices(data, filters));
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
     * Get analysis result actual cost
     *
     * @param {object} data
     * @param {object} filters
     *
     * @returns {float}
     */
    getAnalysesCost(data, filters) {
         let cost = {
            id: null,
            price: 0
        };

        if (!_.isEmpty(data.prices)) {
            let actualPrice = this.getActualPrice(data, filters);

            if (actualPrice) {
                cost.id = actualPrice.id;
                cost.price = actualPrice.cost;
            }
        }
        return cost;
    }

    /**
     * Get analysis actual price
     *
     * @param {object} data
     * @param {object} filters
     *
     * @returns {object}
     */
    getActualPrice(data, filters) {
        let date = `${filters.hasPrice.from}`;
        return data.prices.find((item) => {
            if (item.clinics){
                if (item.clinics.indexOf(filters.hasPrice.clinic) !== -1 &&
                    item.date_from <= date &&
                    (item.date_to >= date || _.isNil(item.date_to)) &&
                    item.cost > 0 &&
                    item.set_type == CONSTANTS.PRICE.SET_TYPE.BASE) {
                    return item;
                }
            }
        });
    }

    /**
     * Get treatment course attribute
     *
     * @returns {string}
     */
    get treatment_course() {
        let appointment = this.appointment;
        if (appointment && appointment.treatment_course_info) {
            return dateFormat(appointment.treatment_course_info.start) +
                   ' - ' +
                   dateFormat(appointment.treatment_course_info.end);
        }
        return '';
    }

    /**
     * log print result
     *
     * @returns Promise
     */
    printed(doctors) {
        let method = this.getOption('methods.printed');
        let route  = this.getRoute('printed');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data   = {
            id: this.id,
        };

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * log download result
     *
     * @returns Promise
     */
    downloaded() {
        let method = this.getOption('methods.downloaded');
        let route  = this.getRoute('downloaded');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data   = {
            id: this.id,
        };

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }
}

export default Result;
