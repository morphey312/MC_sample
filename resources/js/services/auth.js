import store from '../store';

class Auth 
{
    /**
     * Make login request
     * 
     * @param {string} login 
     * @param {string} password 
     * 
     * @returns {Promise}
     */
    login(login, password) {
        return axios.post('/api/v1/auth/login', {
            login,
            password,
        })
            .then((response) => {
                store.commit('login', response.data);
                return response;
            })
            .catch((err) => {
                if (err.response &&  [422, 429].indexOf(err.response.status) !== -1) {
                    return Promise.reject(err.response.data);
                }
                return Promise.reject(err);
            });
    }

    /**
     * Refresh auth toket
     * 
     * @param {*} options 
     * 
     * @returns {Promise}
     */
    refresh(options = {}) {
        return axios.get('/api/v1/auth/refresh', options);
    }

    /**
     * Refresh token if it's older than the given amount of time
     * 
     * @param {number} ms
     * @param {*} options 
     * 
     * @returns {Promise}
     */
    refreshIfOlder(ms, options = {}) {
        if (axios.tokenAge() > ms) {
            return this.refresh(options);
        }
        return Promise.resolve(true);
    }

    hrPortalLogin() {
        return axios.get('/api/v1/employees/hr-portal-login-url')
            .then((response) => response.data.link);
    }
}

export default new Auth();