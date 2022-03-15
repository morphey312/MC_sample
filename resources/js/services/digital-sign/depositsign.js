import lts from '@/services/lts';
import axios from 'axios';

class DepositSignSignature
{
    constructor(clientId) {
        let runtimeData = lts.depositSignRuntime || {
            token: null,
            keys: [],
        };
        this.token = runtimeData.token;
        this.keys = runtimeData.keys;
        this.axios = axios.create({
            baseURL: `https://depositsign.com/api/v1/${clientId}/`,
        });
    }

    isAuthorized() {
        return typeof this.token === 'string';
    }

    authorize(login, password, twoFactorCode = '') {
        return this.axios.post('auth/login', {
            userName: login,
            password: password,
            twoFactorCode: twoFactorCode,
        }).then((result) => {
            this.token = result.data.Token;
            this.keys = result.data.KeysInfo.map((key) => {
                let name = key.KeyName;
                let label = key.KeyName;
                let description = key.KeyName;
                let names = _.uniq(key.CertificatesInformation.map((cert) => cert.FullName));
                let usages = _.uniq(key.CertificatesInformation.map((cert) => cert.KeyUsage));
                if (names.length !== 0) {
                    description = label = names.join(', ');
                }
                if (usages.length !== 0) {
                    description += '(' + usages.join(', ') + ')';
                }
                return {name, label, description};
            });
            this.updateRuntimeState();
        }).catch((error) => {
            if (error.response !== undefined && error.response.status === 401) {
                throw new Error('Unauthorized');
            } else {
                throw new Error('Failed');
            }
        });
    }

    sign(data, key, password, twoFactorCode = '') {
        return this.axios.post('sign/file', {
            fileData: data,
            keyName: key,
            password: password,
            twoFactorCode: twoFactorCode,
        }, {
            headers: {
                'Authorization': `Bearer ${this.token}`,
            },
        }).then((result) => {
            return result.data.SignedData;
        }).catch((error) => {
            throw new Error('Failed');
        });
    }

    getKeys() {
        return this.keys;
    }

    updateRuntimeState() {
        lts.depositSignRuntime = {
            token: this.token,
            keys: this.keys,
        };
    }

    cleanup() {
        this.token = null;
        this.keys = [];
        delete lts.depositSignRuntime;
    }
}

export default new DepositSignSignature(window.appConfig.depositSign.client_id);