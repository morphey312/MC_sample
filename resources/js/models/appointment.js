import BaseModel from '@/models/base-model';
import Patient from '@/models/patient';
import Service from '@/models/appointment/service';
import ServiceRepository from '@/repositories/service';
import AppointmentService from '@/models/appointment/service';
import CONSTANTS from '@/constants';
import moment from 'moment';
import {getServicePrice, getInsurancePrice} from '@/services/appointment/service-price';
import {
    required,
    date,
    greaterThanAttribute,
    attributeEquals,
    maxlen,
    TEXT_MAX_LEN,
} from '@/services/validation';
import {listFormat} from '@/services/format';

let ANALYSIS_SERVICE_ID = null;

/**
 * Appointment model
 */
class Appointment extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            date: '',
            start: '',
            end: '',
            comment: '',
            patient_id: null,
            insurance_policy_id: null,
            card_specialization_id: null,
            operator_id: null,
            doctor_id: null,
            workspace_doctor_id: null,
            doctor_type: '',
            specialization_id: null,
            clinic_id: null,
            treatment_course_id: null,
            appointment_status_id: null,
            status_reason_id: null,
            status_reason_comment: null,
            appointment_delete_reason_id: null,
            delete_reason_comment: '',
            delete_reason_comment_required: false,
            is_first: true,
            is_deleted: false,
            source_id: null,
            workspace_id: null,
            discount_card_id: null,
            legal_entity_id: null,
            services: [],
            assigned_medicines: [],
            ambulance_call: null,
            rejection_reason: '',
            created_by_patient: false,
            updated_at: null,
            do_not_take_payment: false,
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            patient: (value) => this.castToInstance(Patient, value),
            services: (value) => {
                if (value == null || value.length == 0) return [];
                let insuranceCompany = this.insurance_policy ? this.insurance_policy.insurance_company_id : null;

                return value.map(item => {
                    let filters = {
                        hasPrice: {
                            from: this.date,
                            clinic: this.clinic_id,
                        }
                    }

                    if (item.is_base === true && Number(item.saved_cost) !== Number(item.base_cost) * Number(item.quantity) && Number(item.discount) === 0 && Number(item.saved_cost)) {
                        item.price = item.saved_cost;
                    } else {
                        let actualPrice = item.by_policy
                            ? getInsurancePrice(item, filters, CONSTANTS.PRICE.SET_TYPE.INSURANCE, insuranceCompany)
                            : getServicePrice(item, filters, CONSTANTS.PRICE.SET_TYPE.BASE)

                        item.price = actualPrice.price;
                    }
                    return this.castToInstance(Service, item);
                });
            },
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            patient_id: required,
            clinic_id: required,
            doctor_id: required,
            specialization_id: required,
            operator_id: required,
            is_first: required,
            start: required,
            end: required.and(greaterThanAttribute('start')),
            date: required.and(date),
            appointment_status_id: required,
            appointment_delete_reason_id: required.or(attributeEquals('is_deleted', false)),
            delete_reason_comment: required.and(maxlen(TEXT_MAX_LEN)).or(attributeEquals('delete_reason_comment_required', false)),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/appointments',
            fetch: '/api/v1/appointments/{id}',
            update: '/api/v1/appointments/{id}',
            status: '/api/v1/appointments/{id}/change-status',
            updateServices: '/api/v1/appointments/{id}/update-services',
            saveSurgeryEmployees: '/api/v1/appointments/{id}/save-surgery-employees',
        }
    }

    /**
     * Change appointment status
     *
     * @param {string} status
     *
     * @returns {Promise}
     */
    changeSystemStatus(status) {
        return this.changeStatus({system_status: status});
    }

    /**
     * Change appointment status
     *
     * @param {object} data
     *
     * @returns {Promise}
     */
    changeStatus(data) {
        let route  = this.getRoute('status');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);

        let config = () => ({
            url,
            method: this.getSaveMethod(),
            data,
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
     * Add service to appointment
     *
     * @param {object} service
     *
     * @returns {Promise}
     */
    addService(service) {
        if (this.services.some((s) => s.service_id == service.service_id)) {
            return Promise.resolve();
        }
        return this.updateServices([...this.services, service]);
    }

    /**
     * Remove service from appointment
     *
     * @param {number} serviceId
     *
     * @returns {Promise}
     */
    removeService(serviceId) {
        let services = this.services.filter((s) => s.service_id != serviceId);
        if (services.length === this.services.length) {
            return Promise.resolve();
        }
        return this.updateServices(services);
    }

    /**
     * Update services in appointment
     *
     * @param {object} data
     *
     * @returns {Promise}
     */
    updateServices(services) {
        let route  = this.getRoute('updateServices');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let method = this.getUpdateMethod();
        let data   = {services};

        return this.getRequest({method, url, data}).send().then((result) => {
            this.services = result.response.data;
        });
    }

    /**
     * Save surgery employees
     *
     * @param {array} employees
     * @param {bool} allowBlock
     *
     * @returns {Promise}
     */
    saveSurgeryEmployees(employees, allowBlock = true) {
        let route  = this.getRoute('saveSurgeryEmployees');
        let params = this.getRouteParameters();
        let method = this.getUpdateMethod();
        let url    = this.getURL(route, params);
        let data   = {
            surgery_employees: employees,
            allow_block: allowBlock,
        };

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response);
        });
    }

    /**
     * @inheritdoc
     */
    getSaveData(withServices = true) {
        let attributes = super.getSaveData();
        if (withServices) {
            attributes.services = this.cleanupServices(attributes.services);
        } else {
            delete attributes.services;
        }
        return attributes;
    }

    /**
     * Remove odd data from services
     *
     * @param {Array} services
     *
     * @returns {Array}
     */
     cleanupServices(services) {
        return services.map((service) => {
            return _.pick(service, [
                'id',
                'service_id',
                'appointment_id',
                'patient_id',
                'clinic_id',
                'card_specialization_id',
                'price_id',
                'quantity',
                'cost',
                'saved_cost',
                'saved_policy',
                'saved_discount',
                'self_cost',
                'expected_payment',
                'discount',
                'is_base',
                'container_type',
                'card_assignment_id',
                'treatment_assignment_id',
                'issued',
                'is_deleted',
                'not_debt',
                'by_policy',
                'franchise',
                'warranter',
                'source_employee_id',
                'items',
            ]);
        });
    }

    /**
     * @inheritdoc
     */
    save(options = {}, saveServices = true) {
        let data = this.getSaveData(saveServices);
        return super.save({
            data,
            ...options,
        });
    }

    /**
     * Get list of employees with certain role
     *
     * @param {String} role
     *
     * @returns {String}
     */
    getSurgeryEmployee(role) {
        if (this.surgery_employees && this.surgery_employees.length !== 0) {
            return listFormat(this.surgery_employees.filter(e => e.role === role), 'full_name');
        }
        return '';
    }

    /**
     * Get analysis by id
     *
     * @param {Number} id
     *
     * @returns {Object|null}
     */
    getAnalysis(id) {
        for (let container of this.analysis_containers) {
            let analysis = _.find(container.items, (item) => item.analysis_id === id);
            if (analysis !== undefined) {
                return {container, analysis};
            }
        }
        return null;
    }

    /**
     * Add/replace analysis
     *
     * @param {AnalysisResult} analysis
     *
     * @returns {Promise}
     */
    setAnalysis(analysis) {
        for (let container of this.analysis_containers) {
            let index = container.items.findIndex((row) => {
                return analysis.analysis_id === row.analysis_id;
            });
            if (index !== -1) {
                container.items.splice(index, 1, analysis);
                container.payed = _.sumOf(container.items, 'payed');
                return Promise.resolve({container, analysis});
            }
        }
        return this.makeAnalysisContainer().then((container) => {
            container.items.push(analysis);
            container.payed = _.sumOf(container.items, 'payed');
            container.card_assignment_id = analysis.card_assignment_id;
            return {container, analysis};
        });
    }

    /**
     * Remove analysis
     *
     * @param {AnalysisResult} analysis
     */
    unsetAnalysis(analysis) {
        for (let container of this.analysis_containers) {
            let index = container.items.findIndex((row) => {
                return analysis.analysis_id === row.analysis_id;
            });
            if (index !== -1) {
                container.items.splice(index, 1);
                if (container.items.length === 0) {
                    this.analysis_containers = this.analysis_containers
                        .filter((item) => item !== container);
                } else {
                    container.payed = _.sumOf(container.items, 'payed');
                }
                return;
            }
        }
    }

    /**
     * Make new analysis container
     *
     * @returns {Promise}
     */
    makeAnalysisContainer() {
        return this.getAnalysisServiceId().then((serviceId) => {
            let service = new AppointmentService({
                service_id: serviceId,
                container_type: CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES,
            });
            this.services.push(service);
            return service;
        });
    }

    /**
     * Get service related to analyses
     *
     * @returns {Promise}
     */
    getAnalysisServiceId() {
        if (ANALYSIS_SERVICE_ID !== null) {
            return Promise.resolve(ANALYSIS_SERVICE_ID);
        }
        let repository = new ServiceRepository();
        return repository.fetchList({
            payment_destination_mark: CONSTANTS.PAYMENT_DESTINATION.ADDITIONAL_MARK.FOR_ANALYSES,
            clinic: this.clinic_id,
        }, null, 1).then((response) => {
            if (response.length !== 0) {
                ANALYSIS_SERVICE_ID = response[0].id;
                return ANALYSIS_SERVICE_ID;
            }
            throw new Error('There are no services related to analyses');
        });
    }

    /**
     * Get date time
     *
     * @returns {Moment}
     */
    get date_time() {
        return moment(`${this.date} ${this.start}`);
    }

    /**
     * Get list of services
     *
     * @returns {array}
     */
    get service_names() {
        return Object.values(this.services).map((service) => service.name);
    }

    /**
     * Check for base service
     *
     * @returns {bool}
     */
    get has_base_service() {
        let len = this.services.length;
        let hasBase = false;

        for (let i = 0; i < len; i++) {
            if (this.services[i].is_base == true) {
                hasBase = true;
                break;
            }
        }
        return hasBase;
    }

    /**
     * Get employee(doctor) name
     *
     * @returns {string}
     */
    get doctor_name() {
        if (this.doctor && this.doctor.is_employee) {
            return this.doctor.name;
        }
        return '';
    }

    /**
     * Get patient name
     *
     * @returns {string}
     */
    get patient_name() {
        if (this.patient && this.patient.full_name) {
            return this.patient.full_name;
        }
        return '';
    }

    /**
     * Get workspace name
     *
     * @returns {string}
     */
    get workspace_name() {
        if (this.doctor && !this.doctor.is_employee) {
            return this.doctor.name;
        }
        return '';
    }

    /**
     * Get patient card specialization
     *
     * @returns {object}
     */
    get specialization_card() {
        if (this.patient_card) {
            return _.find(
                this.patient_card.specializations,
                (specialization) => specialization.specialization_id == this.card_specialization_id
            );
        }
        return undefined;
    }

    /**
     * Get discount card which is used in appointment
     *
     * @returns {object}
     */
    get discount_card() {
        if (this.discount_card_id) {
            return _.findById(
                _.get(this, 'patient.issued_discount_cards', []),
                this.discount_card_id
            );
        }
        return undefined;
    }

    /**
     * Get service which is analyses containers
     *
     * @returns {Array}
     */
    get analysis_containers() {
        return (this.services || []).filter((service) => {
            return service.container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES;
        });
    }

    /**
     * Update analyses containers
     *
     * @param {Array} list
     */
    set analysis_containers(list) {
        this.services = [
            ...list,
            ...this.regular_services,
        ];
    }

    /**
     * Get list of analyses results
     *
     * @returns {Array}
     */
    get analyses_results() {
        let analyses = [];
        this.analysis_containers.forEach((container) => {
            let itemPaid = container.payed;

            if (container.items.length > 1) {
                itemPaid = itemPaid / container.items.length;
            }

            container.items.forEach((item) => {
                item.payed = itemPaid
                item.has_payments = container.has_payments
                item.service_id = container.id
                analyses.push(item);
            });
        });
        return analyses;
    }

    /**
     * Get regular services
     *
     * @returns {Array}
     */
    get regular_services() {
        return (this.services || []).filter((service) => {
            return _.isVoid(service.container_type);
        });
    }

    /**
     * Update regular services
     *
     * @param {Array} list
     */
    set regular_services(list) {
        this.services = [
            ...list,
            ...this.analysis_containers,
        ];
    }

    /**
     * Get doctor specializations id
     */
    get doctor_specializations() {
        let specializations = [];
        if (this.doctor && this.doctor.specializations) {
            this.doctor.specializations.forEach(row => {
                specializations.push({
                    id: row.id,
                    value: row.name,
                });
            });
        }
        return specializations;
    }

    /**
     * Get surgeonist
     *
     * @returns {string}
     */
    get surgeonist() {
        return this.getSurgeryEmployee(CONSTANTS.EMPLOYEE.SURGERY_ROLES.SURGEONIST);
    }

    /**
     * Get anesthegionist
     *
     * @returns {string}
     */
    get anesthesiologist() {
        return this.getSurgeryEmployee(CONSTANTS.EMPLOYEE.SURGERY_ROLES.ANESTHESIOLOGIST);
    }

    /**
     * Get assistant
     *
     * @returns {string}
     */
    get assistant() {
        return this.getSurgeryEmployee(CONSTANTS.EMPLOYEE.SURGERY_ROLES.ASSISTANT);
    }

    /**
     * Get nurse
     *
     * @returns {string}
     */
    get nurse() {
        return this.getSurgeryEmployee(CONSTANTS.EMPLOYEE.SURGERY_ROLES.NURSE);
    }
}

export default Appointment;
