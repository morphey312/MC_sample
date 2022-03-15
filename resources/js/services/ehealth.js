import axios from 'axios';
import MspRequest from './ehealth/msp-request';
import EmployeeRequest from './ehealth/employee-request';
import ClinicRequest from './ehealth/clinic-request';
import ServiceTypeRequest from './ehealth/service-type-request';
import MspContractRequest from './ehealth/msp-contract-request';
import EmployeeServiceTypeRequest from './ehealth/employee-service-type-request';
import CONSTANT from '@/constants';

const SCOPE_MIS = 'legal_entity:read legal_entity:write event:read medical_program:read';
const SCOPE_OWNER = 'division:activate division:deactivate division:details ' +
    'division:read division:write employee_request:write employee_request:read ' +
    'healthcare_service:read healthcare_service:write contract_request:approve ' +
    'contract_request:create contract_request:read contract_request:sign ' +
    'contract_request:terminate contract:read employee:read ' +
    'employee_role:read employee_role:write legal_entity:read employee:deactivate';
const SCOPE_ADMIN = /*'employee_request:write employee_request:read */'employee_role:read ' +
    'employee_role:write contract_request:approve contract_request:create contract_request:read ' +
    'contract_request:sign contract_request:terminate contract:read';
const SCOPE_HR = 'employee_request:write employee_request:read employee_role:read ' +
    'employee_role:write employee:deactivate';
// const SCOPE_DOCTOR = 'declaration:read person:read';
const SCOPE_DOCTOR = 'declaration:read person:read';
const SCOPE_SPECIALIST = 'division:read person_request:write person_request:read';
const SCOPE_DEFAULT = '';

/**
 * TODO:
 * - restrict user selection for MSP owner (exclude those who is already owner)
 */

class Ehealth
{
    constructor(config) {
        this._axios = axios.create({
            baseURL: config.api_endpoint,
        });
        this._axios.interceptors.request.use((config) => {
            delete config.headers.common['X-Requested-With'];
            return config;
        });
        this._redirectUrl = config.redirect_url;
        this._loginPageUrl = config.login_url;
        this._clientId = config.client_id;
    }

    getRegions(query, limit, page = 1) {
        return this._axios.get('/api/uaddresses/regions', {
            params: {
                name: query,
                page,
                page_size: limit,
            },
        }).then((response) => {
            return response.data.data;
        });
    }

    getDistricts(region, query, limit, page = 1) {
        return this._axios.get('/api/uaddresses/districts', {
            params: {
                name: query,
                region,
                page,
                page_size: limit,
            },
        }).then((response) => {
            return response.data.data;
        });
    }

    getSettlements(region, district, query, limit, page = 1) {
        return this._axios.get('/api/uaddresses/settlements', {
            params: {
                name: query,
                region,
                district,
                page,
                page_size: limit,
            },
        }).then((response) => {
            return response.data.data;
        });
    }

    getStreets(settlementId, query, limit, page = 1) {
        return this._axios.get('/api/uaddresses/streets', {
            params: {
                name: query,
                settlement_id: settlementId,
                page,
                page_size: limit,
            },
        }).then((response) => {
            return response.data.data;
        });
    }

    getPatients(params) {
        return axios.post('/api/v1/ehealth/search-patients', {
            page: 1,
            page_size: 50,
            ...params,
        }).then((response) => {
            return response.data.data.map((entry) => {
                return {
                    id: entry.id,
                    first_name: entry.first_name,
                    last_name: entry.last_name,
                    second_name: entry.second_name,
                    birth_country: entry.birth_country,
                    birth_date: entry.birth_date,
                    birth_settlement: entry.birth_settlement,
                    gender: entry.gender === 'MALE'
                        ? 'male'
                        : (entry.gender === 'FEMALE' ? 'female' : null),
                }
            });
        });
    }

    getLegalEntities(params) {
        return axios.post('/api/v1/ehealth/search-legal-entity', {
            page: 1,
            page_size: 50,
            status: 'ACTIVE',
            ...params,
        }).then((response) => {
            return response.data.data.map((entry) => {
                return {
                    name: entry.edr.name,
                    type: this.getMspTypeByLegalEntityType(entry.type),
                    id: entry.id,
                    edrpou: entry.edrpou,
                }
            });
        });
    }

    getMedicalPrograms(params) {
        return axios.post('/api/v1/ehealth/medical-programs', {
            page: 1,
            page_size: 50,
            is_active: 1,
            ...params,
        }).then((response) => {
            return response.data.data.map((entry) => {
                return {
                    name: entry.name,
                    id: entry.id,
                }
            });
        });
    }

    getContractDetails(type, id) {
        return axios.get('/api/v1/ehealth/contract-details/' + type + '/' + id)
            .then((response) => {
                return response.data;
            });
    }

    getContractRequestDetails(type, requestId) {
        return axios.get('/api/v1/ehealth/contract-request-details/' + type + '/' + requestId)
            .then((response) => {
                return response.data;
            });
    }

    getDocumentContent(url) {
        return axios.post('/api/v1/ehealth/download-document', {
            url,
        }, {
            responseType: 'blob',
        }).then((response) => {
            return response.data;
        });
    }

    initializeOtp(phoneNumber) {
        return axios.post('/api/v1/ehealth/verifications/initialize-otp', {
            phone_number: phoneNumber,
        }).then((response) => {
            return response.data;
        });
    }

    completeOtp(code, phoneNumber) {
        return axios.post('/api/v1/ehealth/verifications/' + phoneNumber + '/complete-otp', {
            code: code,
        }).then((response) => {
            return response.data;
        });
    }

    getPatientAuthenticationMethods(ehealthId) {
        return axios.post('/api/v1/ehealth/patients/get-ehealth-authentications', {
            id: ehealthId,
        }).then((response) => {
            return response.data;
        });
    }

    createMspRequest(msp) {
        let request = new MspRequest(msp, {
            redirectUrl: this._redirectUrl,
            consentText: this.getConsentText(),
        });
        return request.transform().then(() => {
            return request;
        });
    }

    createEmployeeRequest(employee) {
        let request = new EmployeeRequest(employee);
        return request.transform().then(() => {
            return request;
        });
    }

    createClinicRequest(clinic) {
        let request = new ClinicRequest(clinic);
        return request.transform().then(() => {
            return request;
        });
    }

    createServiceTypeRequest(service, clinic) {
        let request = new ServiceTypeRequest(service, clinic);
        return request.transform().then(() => {
            return request;
        });
    }

    createMspContractRequest(contract, msp) {
        let request = new MspContractRequest(contract, msp, this.getNHSConsentText(contract.type));
        return request.transform().then(() => {
            return request;
        });
    }

    createEmployeeServiceTypeRequest(service, employee) {
        let request = new EmployeeServiceTypeRequest(service, employee);
        return request.transform().then(() => {
            return request;
        });
    }

    getNHSConsentText(type) {
        if (type === CONSTANT.EHEALTH.CONTRACT_TYPE.CAPITATION) {
            return this.getCapitationConsentText();
        }
        if (type === CONSTANT.EHEALTH.CONTRACT_TYPE.REIMBURSEMENT) {
            return this.getReimbursementConsentText();
        }
        return null;
    }

    getConsentText() {
        /** @scan-translations-off */
        return 'Цією заявою Заявник висловлює бажання приєднатися до користування сервісами центральної бази e-Health, щоб отримувати дані про медични програми, мидичні висновки, тощо, та забов’язується передавати до центральної бази достовірні дані.';
        /** @scan-translations-on */
    }

    getPatientConsentText() {
        /** @scan-translations-off */
        return `<p>Ви, як медичний працівник закладу охорони здоров’я:</p>
           <ul>
               <li>
                   підтверджуєте, що пацієнта як особу ідентифіковано;
               </li>
               <li>
                   підтверджуєте, що повідомили пацієнту або його представнику мету та
                   підстави обробки його персональних даних.
               </li>
           </ul>

           <p>ПАМ’ЯТКА ПАЦІЄНТУ</p>
           <span>Надаючи код або документи особа чи її представник:</span>
           <ul>
               <li>
                   надає згоду медичному працівнику закладу охорони здоров’я на обробку
                   персональних даних пацієнта, для якого створюється запис в реєстрі пацієнтів Електронної системи охорони здоров’я;
               </li>
               <li>
                   надає згоду медичному працівнику закладу охорони здоров’я створити
                   та при необхідності оновити запис про пацієнта у електронній системі охорони
                   здоров’я від імені особи або її представника.
               </li>
           </ul>`;
        /** @scan-translations-on */
    }

    getNoAccessPhoneNumberInfo(phoneNumber) {
        /** @scan-translations-off */
        return `<span>
                    У разі відсутності доступу до номеру телефона ${phoneNumber} пацієнту необхідно звернутись до
                    <a href="https://edata.e-health.gov.ua/gromadyanam/koristuvacham-ecoz/authentication-form" target="_blank">
                    НСЗУ
                    </a> для скиданняйого методу аутентифікації.
                </span>`;
        /** @scan-translations-on */
    }

    getCapitationConsentText() {
        /** @scan-translations-off */
        return 'Цією заявою Заявник висловлює бажання укласти договір про медичне обслуговування населення за програмою державних гарантій медичного обслуговування населення (далі – Договір) на умовах, визначених в оголошенні про укладення договорів про медичне обслуговування населення (далі – Оголошення). Заявник підтверджує, що: 1. на момент подання цієї заяви Заявник має чинну ліцензію на провадження господарської діяльності з медичної практики та відповідає ліцензійним умовам з медичної практики; 2. Заявник надає медичні послуги, пов’язані з первинною медичною допомогою (далі – ПМД); 3. Заявник зареєстрований в електронній системі охорони здоров’я (далі – Система); 4. уповноважені особи та медичні працівники, які будуть залучені до виконання Договору, зареєстровані в Системі та отримали електронний цифровий підпис (далі – ЕЦП); 5. в кожному місці надання медичних послуг Заявника наявне матеріально-технічне оснащення, передбачене розділом І Примірного табелю матеріально-технічного оснащення закладів охорони здоров’я та фізичних осіб – підприємців, які надають ПМД, затвердженого наказом Міністерства охорони здоров’я України від 26 січня 2018 року №148; 6. установчими або іншими документами не обмежено право керівника Заявника підписувати договори від імені Заявника без попереднього погодження власника. Якщо таке право обмежено, у тому числі щодо укладання договорів, ціна яких перевищує встановлену суму, Заявник повідомить про це Національну службу здоров’я та отримає необхідні погодження від власника до моменту підписання договору зі сторони Заявника; 7. інформація, зазначена Заявником у цій Заяві та доданих до неї документах, а також інформація, внесена Заявником (його уповноваженими особами) до Системи, є повною та достовірною. Заявник усвідомлює, що у разі зміни інформації, зазначеної Заявником у цій заяві та (або) доданих до неї документах Заявник зобов’язаний повідомити про такі зміни НСЗУ протягом трьох робочих днів з дня настання таких змін шляхом надсилання інформації про такі зміни на електронну пошту dohovir@nszu.gov.ua, з одночасним внесенням таких змін в Систему. Заявник усвідомлює, що законодавством України передбачена відповідальність за подання недостовірної інформації органам державної влади.';
        /** @scan-translations-on */
    }

    getReimbursementConsentText() {
        /** @scan-translations-off */
        return 'Цією заявою Заявник висловлює бажання укласти договір про реімбурсацію з Національною службою здоров\'я України та підтверджує, що на момент подання цієї заяви Заявник провадить господарську діяльність на підставі ліцензії на провадження господарської діяльності з роздрібної торгівлі лікарськими засобами та відповідає ліцензійним умовам. Заявник усвідомлює, що у разі зміни інформації, зазначеної Заявником у цій заяві та (або) доданих до неї документах Заявник зобов\'язаний повідомити про такі зміни НСЗУ протягом трьох робочих днів з дня настання таких змін шляхом надсилання інформації про такі зміни на електронну пошту dohovir@nszu.gov.ua, з одночасним внесенням таких змін в Систему. Заявник усвідомлює, що законодавством України передбачена відповідальність за подання недостовірної інформації органам державної влади.';
        /** @scan-translations-on */
    }

    getMisLoginUrl() {
        let redirectUri = encodeURIComponent(this._redirectUrl);
        let scope = encodeURIComponent(SCOPE_MIS);
        return `${this._loginPageUrl}?response_type=code&client_id=${this._clientId}&redirect_uri=${redirectUri}&scope=${scope}`;
    }

    getMisInitUrl() {
        return axios.get('/api/v1/ehealth/init-url')
            .then((response) => {
                let data = response.data;
                let redirectUri = encodeURIComponent(data.redirect_uri);
                let scope = encodeURIComponent(SCOPE_MIS);
                return `${this._loginPageUrl}?response_type=code&client_id=${this._clientId}&redirect_uri=${redirectUri}&scope=${scope}`;
            })
            .catch((err) => {
                if (err.response && 400 === err.response.status) {
                    return false;
                }
                return Promise.reject(err);
            });
    }

    getMspLoginUrl(email) {
        return axios.post('/api/v1/ehealth/login-url', {
            email,
        })
            .then((response) => {
                let data = response.data;
                let redirectUri = encodeURIComponent(data.redirect_uri);
                let queryEmail = encodeURIComponent(email);
                let scope = encodeURIComponent(this.getEmployeeScope(data.type));
                return `${this._loginPageUrl}?response_type=code&client_id=${data.client_id}&redirect_uri=${redirectUri}&email=${queryEmail}&scope=${scope}`;
            })
            .catch((err) => {
                if (err.response && 422 === err.response.status) {
                    return Promise.reject(err.response.data);
                }
                return Promise.reject(err);
            });
    }

    getEmployeeScope(type) {
        if (type === CONSTANT.EHEALTH.EMPLOYEE_TYPE.DOCTOR) {
            return SCOPE_DOCTOR;
        } else if (type === CONSTANT.EHEALTH.EMPLOYEE_TYPE.SPECIALIST) {
            return SCOPE_SPECIALIST;
        } else if (type === CONSTANT.EHEALTH.EMPLOYEE_TYPE.OWNER) {
            return SCOPE_OWNER;
        } else if (type === CONSTANT.EHEALTH.EMPLOYEE_TYPE.HR) {
            return SCOPE_HR;
        } else if (type === CONSTANT.EHEALTH.EMPLOYEE_TYPE.ADMIN) {
            return SCOPE_ADMIN;
        } else {
            return SCOPE_DEFAULT;
        }
    }

    getMspTypeByLegalEntityType(type) {
        switch (type) {
            case 'EMERGENCY': return 'emergency';
            case 'OUTPATIENT': return 'outpatient';
            case 'PHARMACY': return 'pharmacy';
            case 'PRIMARY_CARE': return 'primary_care';
        }
        return type;
    }

    initializeOtp(phone) {
        return axios.post('/api/v1/ehealth/initialize-otp', {
            phone_number: phone,
        }).then((response) => {
            return response.data;
        });
    }

    completeOtp(phone, code) {
        return axios.post('/api/v1/ehealth/' + phone + '/complete-otp', {
            code: code,
        }).then((response) => {
            return response.data;
        });
    }
}

export default new Ehealth(window.appConfig.ehealth);
