import BaseModel from '@/models/base-model';
import Blank from '@/models/clinic/blank';
import ServiceType from '@/models/clinic/service-type'
import EhealthAddress from '@/models/ehealth/address';
import CONSTANTS from '@/constants';

import {
    maxlen,
    required,
    phoneNumber,
    email,
    missing,
    numeric,
    lte,
    gte,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
} from '@/services/validation';

/**
 * Clinic model
 */
class Clinic extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Clinic';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            msp_id: null,
            ehealth_id: null,
            active_in_ehealth: false,
            currency: null,
            country_id: null,
            city: null,
            name: null,
            signer: null,
            signer_position: null,
            official_name: null,
            official_additional: null,
            contact_phone: null,
            additional_contact_phone: null,
            email: null,
            working_hours: null,
            map_link: null,
            status: null,
            voip_queue: null,
            medicine_stores: [],
            blanks: [],
            questionnaire_blank: null,
            image_id: null,
            group_id: null,
            money_reciever_id: null,
            money_reciever: [],
            analysis_check_url: '',
            kind: null,
            address: {},
            reception_address: {},
            lat: null,
            lng: null,
            ehealth_request: null,
            authority_name: null,
            short_name: null,
            not_round_cost: false,
            need_apply_city: null,
            export_patients_contacts: false,
            medicine_firm_id: null,
            is_default: false,
            works_with_apteka24: false,
            show_on_site: false,
            secure_analysis: false,
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            blanks: (val) => {
                if (this.blanks === null || this.blanks.length == 0) {
                    return [
                        new Blank({type: CONSTANTS.CLINIC.BLANK_TYPE.HEADER}),
                        new Blank({type: CONSTANTS.CLINIC.BLANK_TYPE.FOOTER})
                    ];
                }
                return val;
            },
            address: (val) => this.initSubModel(EhealthAddress, val),
            reception_address: (val) => this.initSubModel(EhealthAddress, val),
            service_types: (value) => _.isArray(value) ? value.map((type) => this.initSubModel(ServiceType, type)) : [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            signer: maxlen(STRING_MAX_LEN),
            signer_position: maxlen(STRING_MAX_LEN),
            official_name: required.and(maxlen(STRING_MAX_LEN)),
            official_additional: maxlen(TEXT_MAX_LEN),
            contact_phone: required.and(phoneNumber),
            additional_contact_phone: phoneNumber.or(missing),
            email: required.and(email),
            map_link: maxlen(TEXT_MAX_LEN),
            short_name: maxlen(STRING_MAX_LEN),
            currency: required,
            status: required,
            address: (value) => {
                if (value.country_code === CONSTANTS.COUNTRY_CODE.UA) {
                    return this.validateSubmodel(value, null, ['country_code', 'region', 'settlement_id']);
                }
                return this.validateSubmodel(value, null, ['country_code', 'address']);
            },
            lat: numeric.and(gte(-90).and(lte(90))).or(missing),
            lng: numeric.and(gte(-180).and(lte(180))).or(missing),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/clinics',
            fetch: '/api/v1/clinics/{id}',
            update: '/api/v1/clinics/{id}',
            delete: '/api/v1/clinics/{id}',
            getBlanks: '/api/v1/clinics/{id}/blanks',
        }
    }

    /**
     * @inheritdoc
     */
    options() {
        return {
            ...super.options(),
            methods: {
                getBlanks: 'GET',
            }
        }
    }

    /**
     * Fetch related blanks
     */
    getBlanks() {
        let method = this.getOption('methods.getBlanks');
        let route = this.getRoute('getBlanks');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data;

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        if (attributes.address && attributes.address.country_id) {
            attributes.country_id = attributes.address.country_id;
        }
        return attributes;
    }

    /**
     * Get header blank
     *
     * @returns Blank
     */
    get header_blank() {
        return this.blanks.find(blank => blank.type == CONSTANTS.CLINIC.BLANK_TYPE.HEADER);
    }

    /**
     * Get footer blank
     *
     * @returns Blank
     */
    get footer_blank() {
        return this.blanks.find(blank => blank.type == CONSTANTS.CLINIC.BLANK_TYPE.FOOTER);
    }
}

export default Clinic;
