import axios from 'axios';
import store from '@/store';
import serverTime from '@/services/server-time';
import router from '@/router';
import auth from '@/services/auth';
import translationServer from '@/services/translation';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let numPendingRequests = 0;
let refreshTokenTimeout = null;
let isTokenRefresh = false;
let tokenUpdatedAt = 0;

const TOKEN_REFRESH_PERIOD = (window.appConfig.tokenTTL - 5) * 60;

const sheduleTokenRefresh = (time) => {
    clearTimeout(refreshTokenTimeout);
    refreshTokenTimeout = setTimeout(refreshToken, time * 1000);
}

const refreshToken = () => {
    if (!isTokenRefresh && store.state.token) {
        return _.waitUntil(() => numPendingRequests === 0).then(() => {
            isTokenRefresh = true;
            auth.refresh({isTokenRequest: true}).finally(() => {
                isTokenRefresh = false;
            });
        });
    }
}

const configureRequest = (config) => {
    if (store.state.token) {
        config.headers.authorization = store.state.token;
        config.headers['Accept-Language'] = translationServer.lang;
    }
    numPendingRequests++;
    return config;
}

const waitFreshToken = (config) => {
    if (isTokenRefresh && !config.isTokenRequest) {
        return _.waitUntil(() => !isTokenRefresh).then(() => {
            return configureRequest(config);
        });
    }
    return Promise.resolve(configureRequest(config));
}

export default (error) => { 
    window.axios.interceptors.request.use((config) => {
        return waitFreshToken(config);
    });

    window.axios.interceptors.response.use(
        (response) => {
            if (response.headers.authorization) {
                store.commit('updateToken', response.headers.authorization);
                sheduleTokenRefresh(TOKEN_REFRESH_PERIOD);
                tokenUpdatedAt = Date.now();
            }
            if (response.headers.date) {
                serverTime.sync(response.headers['server-time']);
            }
            numPendingRequests--;
            return response;
        },
        (err) => {
            numPendingRequests--;
            if(err.response && err.response.status === 401) {
                store.commit('logout');
                router.push({name: 'login'});
            } else if(err.response && err.response.status === 403) {
                error(__('У вас нет прав для выполнения данной операции'));
            } else if(err.response && err.response.status === 422) {
                return Promise.reject(err);
            } else if(err.response && err.response.status === 423) {
                if (err.config !== undefined && err.config.method === 'delete') {
                    error(__('Невозможно удалить эту запись, так как она используется другими записями'));
                }
            } else if(err.response && err.response.status === 409) {
                error(__('Данная запись была временно заблокирована для изменения. Пожалуйста, обновите информацию в записи и попробуйте повторить попытку.'));
            } else if(err.response && err.response.status >= 500) {
                error(__('Во время обработки запроса произошла ошибка. Пожалуйста, попробуйте повторить попытку немного позже.'));
            }
            return Promise.reject(err);
        }
    );

    window.axios.countPendingRequests = () => {
        return numPendingRequests;
    }

    window.axios.tokenAge = () => {
        return Date.now() - tokenUpdatedAt;
    }
}