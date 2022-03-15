import BaseModel from '@/models/base-model';
import {
    date, email, gte, lte, maxlen, missing, numeric, phoneNumber, required, STRING_MAX_LEN, TEXT_MAX_LEN,
} from '@/services/validation';
import CONSTANTS from "@/constants";

class PriceAgreementAct extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            type: null,
            status: null,
            date_from: null,
            employee_id: null,
            act_prices: [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            type: required.and(maxlen(STRING_MAX_LEN)),
            status: required.and(maxlen(STRING_MAX_LEN)),
            employee_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/price-agreement-act',
            fetch: '/api/v1/price-agreement-act/{id}',
            update: '/api/v1/price-agreement-act/{id}',
            status: '/api/v1/price-agreement-act/{id}/change-status',
            approve: '/api/v1/price-agreement-act/{id}/approve',
            delete: '/api/v1/price-agreement-act',
        }
    }

    /**
     * Change transfer sheet status
     *
     * @param {object} data
     *
     * @returns {Promise}
     */
    changeStatus(status) {
        let route  = this.getRoute('status');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);

        let config = () => ({
            url,
            method: this.getSaveMethod(),
            data: {status},
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

    /**
     * Change transfer sheet status
     *
     * @param {object} data
     *
     * @returns {Promise}
     */
    approve(date) {
        let route  = this.getRoute('approve');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);

        let config = () => ({
            url,
            method: this.getSaveMethod(),
            data: {date},
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

export default PriceAgreementAct;
