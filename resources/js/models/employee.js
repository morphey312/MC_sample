import BaseModel from '@/models/base-model';
import User from '@/models/user';
import EmployeeClinic from '@/models/employee/clinic';
import EmployeeDocument from '@/models/employee/document';
import EmployeeEducation from '@/models/employee/education';
import EmployeeQualification from '@/models/employee/qualification';
import EmployeeSpeciality from '@/models/employee/speciality';
import EmployeeScienceDegree from '@/models/employee/science-degree';
import EmployeeServiceType from '@/models/employee/service-type';
import OperatorBonus from '@/models/employee/operator-bonus';
import {
    required,
    maxlen,
    phoneNumber,
    numeric,
    missing,
    email,
    STRING_MAX_LEN,
    TEXT_MAX_LEN
} from '@/services/validation';

class Employee extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Employee';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            ehealth_id: null,
            ehealth_request_status: null,
            phone: null,
            additional_phone: null,
            email: null,
            first_name: null,
            last_name: null,
            middle_name: null,
            birth_date: null,
            gender: null,
            is_translator: false,
            system_status: null,
            tax_id: null,
            no_tax_id: false,
            experience: null,
            about: null,
            user: {},
            ehealth_request: null,
            preferred_language: 'ua',
            copy_to_portal: false,
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            user: (value) => this.initSubModel(User, value),
            employee_clinics: (value) => _.isArray(value) ? value.map((clinic) => this.initSubModel(EmployeeClinic, clinic)) : [],
            employee_documents: (value) => _.isArray(value) ? value.map((doc) => this.initSubModel(EmployeeDocument, doc)) : [],
            employee_educations: (value) => _.isArray(value) ? value.map((education) => this.initSubModel(EmployeeEducation, education)) : [],
            employee_qualifications: (value) => _.isArray(value) ? value.map((qualification) => this.initSubModel(EmployeeQualification, qualification)) : [],
            employee_specialities: (value) => _.isArray(value) ? value.map((speciality) => this.initSubModel(EmployeeSpeciality, speciality)) : [],
            employee_science_degrees: (value) => _.isArray(value) ? value.map((degree) => this.initSubModel(EmployeeScienceDegree, degree)) : [],
            employee_service_types: (value) => _.isArray(value) ? value.map((type) => this.initSubModel(EmployeeServiceType, type)) : [],
            operator_bonus: (value) => this.castToInstance(OperatorBonus, value, true),
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            phone: required.and(phoneNumber),
            additional_phone: phoneNumber.or(missing),
            email: email.or(missing),
            first_name: required.and(maxlen(STRING_MAX_LEN)),
            last_name: required.and(maxlen(STRING_MAX_LEN)),
            middle_name: maxlen(STRING_MAX_LEN),
            experience: numeric.or(missing),
            about: maxlen(TEXT_MAX_LEN),
            user: (value) => this.validateSubmodel(value),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/employees',
            fetch: '/api/v1/employees/{id}',
            update: '/api/v1/employees/{id}',
            delete: '/api/v1/employees/{id}',
            saveFeaturedAnalysis: '/api/v1/employees/toggle-featured-analysis',
            saveFeaturedMedicine: '/api/v1/employees/toggle-featured-medicine',
            saveFeaturedService: '/api/v1/employees/toggle-featured-service',
            saveFeaturedSpecialization: '/api/v1/employees/toggle-featured-specialization',
            saveOutclinicMedicines: '/api/v1/employees/save-outclinic-medicines',
            saveOutclinicSpecializations: '/api/v1/employees/save-outclinic-specializations',
            saveOutclinicDiagnostics: '/api/v1/employees/save-outclinic-diagnostics',
            fetchFeaturedAnalyses: '/api/v1/employees/featured-analyses',
            fetchFeaturedMedicines: '/api/v1/employees/featured-medicines',
            fetchFeaturedServices: '/api/v1/employees/featured-services',
            fetchFeaturedSpecializations: '/api/v1/employees/featured-specializations',
            fetchCashboxes: '/api/v1/employees/{id}/cashboxes',
            fetchOutclinicMedicines: '/api/v1/employees/fetch-outclinic-medicines',
            fetchOutclinicSpecializations: '/api/v1/employees/fetch-outclinic-specializations',
            fetchOutclinicDiagnostics: '/api/v1/employees/fetch-outclinic-diagnostics',
            saveOperatorBonus: '/api/v1/employees/{id}/save-operator-bonus',
        }
    }

    /**
     * @inheritdoc
     */
    getDefaultMethods() {
        return {
            ...super.getDefaultMethods(),
            saveFeaturedAnalysis: 'POST',
            saveFeaturedMedicine: 'POST',
            saveFeaturedService: 'POST',
            saveFeaturedSpecialization: 'POST',
            saveOutclinicMedicines: 'POST',
            saveOutclinicSpecializations: 'POST',
            saveOutclinicDiagnostics: 'POST',
            saveOperatorBonus: 'POST',
            fetchFeaturedAnalyses: 'GET',
            fetchFeaturedMedicines: 'GET',
            fetchFeaturedServices: 'GET',
            fetchFeaturedSpecializations: 'GET',
            fetchCashboxes: 'GET',
            fetchOutclinicMedicines: 'GET',
            fetchOutclinicSpecializations: 'GET',
            fetchOutclinicDiagnostics: 'GET'
        };
    }

    /**
     * Save featured analysis
     *
     * @param {number} analysis_id
     *
     * @return Promise
     */
    saveFeaturedAnalysis(analysis_id) {
        let data   = {
            id: this.id,
            featured: analysis_id,
        };
        return this.saveInternalFeatured(data, 'saveFeaturedAnalysis');
    }

    /**
     * Save featured medicine
     *
     * @param {number} medicine_id
     *
     * @return Promise
     */
    saveFeaturedMedicine(medicine_id) {
        let data   = {
            id: this.id,
            featured: medicine_id,
        };
        return this.saveInternalFeatured(data, 'saveFeaturedMedicine');
    }

    /**
     * Save featured service
     *
     * @param {number} service_id
     *
     * @return Promise
     */
    saveFeaturedService(service_id) {
        let data   = {
            id: this.id,
            featured: service_id,
        };
        return this.saveInternalFeatured(data, 'saveFeaturedService');
    }

    /**
     * Save featured specialization
     *
     * @param {number} specialization_id
     *
     * @return Promise
     */
    saveFeaturedSpecialization(specialization_id) {
        let data   = {
            id: this.id,
            featured: specialization_id,
        };
        return this.saveInternalFeatured(data, 'saveFeaturedSpecialization');
    }

    /**
     * Save featured
     *
     * @param {object} data
     * @param {string} routeMethod
     */
    saveInternalFeatured(data, routeMethod) {
        let method = this.getOption(`methods.${routeMethod}`);
        let route  = this.getRoute(routeMethod);
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Save outclinic medicines
     *
     * @param array list
     * @param object newLock
     *
     * @return Promise
     */
    saveOutclinicMedicines(medicines) {
        if (medicines.length == 0) {
            return Promise.resolve();
        }
        let data = {medicines};
        return this.saveInternalFeatured(data, 'saveOutclinicMedicines');
    }

    /**
     * Save outclinic specializations
     *
     * @param array list
     * @param object newLock
     *
     * @return Promise
     */
    saveOutclinicSpecializations(outclinicSpecializations) {
    	if (outclinicSpecializations.length == 0) {
            return Promise.resolve();
        }
        let data = {outclinicSpecializations};
        return this.saveInternalFeatured(data, 'saveOutclinicSpecializations');
    }

    /**
     * Save outclinic specializations
     *
     * @param array list
     * @param object newLock
     *
     * @return Promise
     */
    saveOutclinicDiagnostics(outclinicDiagnostics) {
    	if (outclinicDiagnostics.length == 0) {
            return Promise.resolve();
        }
        let data = {outclinicDiagnostics};
        return this.saveInternalFeatured(data, 'saveOutclinicDiagnostics');
    }
    /**
     * Fetch doctor featured analyses
     *
     * @param {object} filters
     *
     * @return Promise
     */
    fetchFeaturedAnalyses(filters, scopes = [], params = {}) {
        scopes = ['default', 'prices_for_query', ...scopes];
        return this.fetchInternalLists(filters, 'fetchFeaturedAnalyses', scopes, params);
    }

    /**
     * Fetch doctor featured medicines
     *
     * @param {object} filters
     *
     * @return Promise
     */
    fetchFeaturedMedicines(filters) {
        return this.fetchInternalLists(filters, 'fetchFeaturedMedicines');
    }

    /**
     * Fetch doctor featured services
     *
     * @param {object} filters
     * @param {array} scopes
     * @param {object} params
     *
     * @return Promise
     */
    fetchFeaturedServices(filters, scopes = [], params = {}) {
        scopes = ['default', 'prices_for_query', ...scopes];
        return this.fetchInternalLists(filters, 'fetchFeaturedServices', scopes, params);
    }

    /**
     * Fetch doctor featured specializations
     *
     * @param {object} filters
     *
     * @return Promise
     */
    fetchFeaturedSpecializations(filters) {
        return this.fetchInternalLists(filters, 'fetchFeaturedSpecializations');
    }

    /**
     * Fetch featured list
     *
     * @param {object} filters
     * @param {string} routeMethod
     *
     * @return Promise
     */
    fetchInternalLists(filters, routeMethod, scopes = null, queryParams = null) {
        let method = this.getOption(`methods.${routeMethod}`);
        let route  = this.getRoute(routeMethod);
        let params = this.getRouteParameters();
        let url    = this.getUrlWithQueryParams(route, params, filters, scopes, queryParams);
        let data   = {};

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Fetch employee cashboxes
     *
     * return {Promise}
     */
    fetchCashboxes(filters) {
        return this.fetchInternalLists(filters, 'fetchCashboxes');
    }

    /**
     * Fetch employee outclinic medicines
     *
     * @returns {Promise}
     */
    fetchOutclinicMedicines() {
        return this.fetchInternalLists({}, 'fetchOutclinicMedicines');
    }

    /**
     * Fetch employee outclinic specializations
     *
     * @returns {Promise}
     */
    fetchOutclinicSpecializations() {
    	return this.fetchInternalLists({}, 'fetchOutclinicSpecializations');
    }

    /**
     * Fetch employee outclinic specializations
     *
     * @returns {Promise}
     */
    fetchOutclinicDiagnostics() {
    	return this.fetchInternalLists({}, 'fetchOutclinicDiagnostics');
    }
    /**
     * Save operator bonus norms
     *
     * @returns {Promise}
     */
    saveOperatorBonus() {
        let method = this.getOption(`methods.saveOperatorBonus`);
        let route  = this.getRoute('saveOperatorBonus');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data   = {
            operator_bonus: this.operator_bonus,
        };

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Get names of clinics employee belongs to
     *
     * @returns {array}
     */
    get clinic_names() {
        return this.employee_clinics
            ? this.employee_clinics
                .map((clinic) => clinic.clinic_name)
            : [];
    }

    /**
     * Get id of clinics employee belongs to
     *
     * @returns {array}
     */
    get clinic_ids() {
        return this.employee_clinics
            ? this.employee_clinics
                .map((clinic) => clinic.clinic_id)
            : [];
    }

    /**
     * Get positions of employee accross all clinics
     *
     * @returns {array}
     */
    get position_names() {
        return this.employee_clinics
            ? _.uniq(
                this.employee_clinics
                    .map((clinic) => clinic.position_name)
            )
            : [];
    }

    /**
     * Get id of positions of employee accross all clinics
     *
     * @returns {array}
     */
    get position_ids() {
        return this.employee_clinics
            ? _.uniq(
                this.employee_clinics
                    .map((clinic) => clinic.position_id)
            )
            : [];
    }

    /**
     * Get specializations of employee accross all clinics
     *
     * @returns {array}
     */
    get specialization_names() {
        return this.employee_clinics
            ? _.uniq(
                _.flatten(
                    this.employee_clinics
                        .map((clinic) => {
                            return clinic.specialization_names;
                        })
                    )
                )
            : [];
    }

     /**
     * Get status of employee accross all clinics
     *
     * @returns {array}
     */
    get status_names() {
        return this.employee_clinics
            ? _.uniq(
                this.employee_clinics
                    .map((clinic) => clinic.status)
            )
            : [];
    }

    /**
     * Get employee full name
     *
     * @returns {string}
     */
    get full_name() {
        return [
            this.last_name,
            this.first_name,
            this.middle_name,
        ]
            .filter(_.isFilled)
            .join(' ');
    }

    /**
     * Set employee full name
     *
     * @param {string} val
     */
    set full_name(val) {
        let parts = val.split(' ').filter(p => p !== '');
        this.last_name = parts.shift();
        this.first_name = parts.shift();
        this.middle_name = parts.join(' ');
    }
}

export default Employee;
