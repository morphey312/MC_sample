import axios from "axios";
import {log} from "qrcode/lib/core/galois-field";

class CheckBox
{
    constructor() {
        this._axios = axios.create({
            baseURL: document.location.href.split('#')[0],
        });
    }

    login (login,password,cashboxKey) {
        return this._axios.post('/api/v1/checkbox/login', {
            login: login,
            password: password,
            cashboxKey: cashboxKey,
        }).then((response) => {
            return response.data;
        })
    }

    closeShifts (accessToken) {
        return this._axios.post('/api/v1/checkbox/close-shifts', {
            access_token: accessToken
        }).then((response) => {
            return response.data;
        })
    }

    getCashierBalance (accessToken) {
        return this._axios.post('/api/v1/checkbox/cashier-balance', {
            access_token: accessToken
        }).then((response) => {
            return response.data;
        })
    }

    getXReport (accessToken) {
        return this._axios.post('/api/v1/checkbox/x-report', {
            access_token: accessToken
        }).then((response) => {
            return response.data;
        })
    }

    getZReport (accessToken) {
        return this._axios.post('/api/v1/checkbox/z-report', {
            access_token: accessToken
        }).then((response) => {
            return response.data;
        })
    }

    createCurrencyCheck(accessToken,amount) {
        return this._axios.post('/api/v1/checkbox/currency-check', {
            access_token: accessToken,
            amount: amount,
        }).then((response) => {
            return response.data;
        })
    }

    downloadCheck (paymentId,serviceId = null, accessToken, checkboxCheckId) {
        return this._axios.post('/api/v1/checkbox/download-check', {
            payment_id: paymentId,
            access_token: accessToken,
            checkbox_check_id: checkboxCheckId,
            service_id: serviceId,
        }).then((response) => {
            return response.data;
        })
    }
}

export default new CheckBox();
