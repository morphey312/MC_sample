import BaseModel from '@/models/base-model';
import ProcessLog from '@/models/calls/process-log';
import CONSTANTS from '@/constants';

import {
    required,
} from '@/services/validation';

class SiteEnquiry extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            category: null,
            name: null,
            email: null,
            phone_number: null,
            card_number: null,
            clinic_id: null,
            specialization_id: null,
            doctor_id: null,
            operator_id: null,
            subject: null,
            notes: null,
            referer: null,
            date: null,
            cost: null,
            payment_status: null,
            order_id: null,
            client_id:null,
            token:null,
            payed: 0,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            operator_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            process: (value) => this.castToInstance(ProcessLog, value, true),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/site-enquiries',
            fetch: '/api/v1/site-enquiries/{id}',
            update: '/api/v1/site-enquiries/{id}',
            delete: '/api/v1/site-enquiries/{id}',
            updateServices: '/api/v1/site-enquiries/{id}/update-services',
        }
    }

    /**
     * Save services
     *
     * @param {array} services
     *
     * @return Promise
     */
    updateServices(services) {
        let method = this.getOption('methods.create');
        let route  = this.getRoute('updateServices');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data = {services: this.prepareService(services)};

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Get only services attributes
     *
     * @param {array} services
     *
     * @returns array
     */
    prepareService(services) {
        return services.map(service => {
            return {
                id: service.id,
                service_id: service.service_id,
                service_type: service.service_type,
                site_enquiry_id: service.site_enquiry_id,
                cost: service.cost,
                discount: service.discount,
                payed_amount: service.payed_amount,
                appointment_id: service.appointment_id,
            };
        });
    }

    /**
     * Get clinic name
     */
    get clinic_name() {
        return this.attributes.clinic_name;
    }

    /**
     * Get card number
     *
     * @returns string
     */
    get card_number() {
        return this.attributes.card_number;
    }

    /**
     * Get specialization name
     */
    get specialization_name() {
        return this.attributes.specialization_name;
    }

    /**
     * Get doctor name
     */
    get doctor_name() {
        return this.attributes.doctor_name;
    }

    /**
     * Get services
     *
     * @returns array
     */
    get service_list() {
        return this.attributes.services
            ? this.attributes.services.filter(item => {
                return item.service_type === CONSTANTS.SITE_ENQUIRY.SERVICE_TYPE.SERVICE && item.appointment_id == null
            })
            : [];
    }

    /**
     * Get used services
     *
     * @returns array
     */
    get used_service_list() {
        return this.attributes.services
            ? this.attributes.services.filter(item => {
                return item.service_type === CONSTANTS.SITE_ENQUIRY.SERVICE_TYPE.SERVICE && item.appointment_id != null
            })
            : [];
    }

    /**
     * Get unpaid services
     *
     * @returns array
     */
     get unpaid_service_list() {
        return this.attributes.unpaid_services
            ? this.attributes.unpaid_services.filter(item => {
                return item.service_type === CONSTANTS.SITE_ENQUIRY.SERVICE_TYPE.SERVICE && item.appointment_id == null
            }).map(item => {
                item.payed_amount = 0;
                return item;
            })
            : [];
    }

    /**
     * Get analyses
     *
     * @returns array
     */
    get analysis_list() {
        return this.attributes.services
            ? this.attributes.services.filter(item => {
                return item.service_type === CONSTANTS.SITE_ENQUIRY.SERVICE_TYPE.ANALYSIS && item.appointment_id == null
            })
            : [];
    }

    /**
     * Get analyses
     *
     * @returns array
     */
    get used_analysis_list() {
        return this.attributes.services
            ? this.attributes.services.filter(item => {
                return item.service_type === CONSTANTS.SITE_ENQUIRY.SERVICE_TYPE.ANALYSIS && item.appointment_id != null
            })
            : [];
    }

    /**
     * Get unpaid services
     *
     * @returns array
     */
     get unpaid_analysis_list() {
        return this.attributes.unpaid_services
            ? this.attributes.unpaid_services.filter(item => {
                return item.service_type === CONSTANTS.SITE_ENQUIRY.SERVICE_TYPE.ANALYSIS && item.appointment_id == null
            }).map(item => {
                item.payed_amount = 0;
                return item;
            })
            : [];
    }

    /**
     * Get has available services to add to appointment
     *
     * @returns bool
     */
    get has_available_services() {
        return (this.service_list && this.service_list.length != 0) || (this.analysis_list && this.analysis_list.length != 0);
    }

    /**
     * Get has used services in appointments
     *
     * @returns bool
     */
    get has_used_services() {
        return (this.attributes.services
            ? this.attributes.services.filter(item => {
                return item.appointment_id != null
              })
            : []
            ).length != 0;
    }
}

export default SiteEnquiry;
