import BaseModel from '@/models/base-model';
import ContactDetails from '@/models/patient/contact-details';
import PatientCard from '@/models/patient/card/card';
import IssuedDiscountCard from '@/models/patient/issued-discount-card';
import Relative from '@/models/patient/relative';
import InsurancePolicy from '@/models/patient/insurance-policy';
import {
    required,
    date,
    attributeEquals,
    missing,
    maxlen,
    requiredArray,
    STRING_MAX_LEN,
    TEXT_MAX_LEN
} from '@/services/validation';
import {numberFormat} from '@/services/format';
import CONSTANTS from '@/constants';
import moment from "moment";

class Patient extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Patient';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            ehealth_id: null,
            firstname: null,
            firstname_latin: null,
            lastname: null,
            lastname_latin: null,
            middlename: null,
            passport_no: null,
            gender: null,
            status: 'patient',
            med_insurance: 'n_a',
            location: null,
            street: null,
            house_number: null,
            apartment_number: null,
            birthday: null,
            comment: null,
            sms: true,
            mailing: false,
            mailing_analysis: true,
            black_mark: false,
            black_mark_reason: null,
            black_mark_comment: null,
            is_skk: false,
            skk_reason: null,
            skk_comment: null,
            source_id: null,
            is_attention: false,
            attention_comment: null,
            is_confirmed: false,
            contact_details: {},
            clinics: [],
            cards: [],
            issued_discount_cards: [],
            assigned_analyses: [],
            relatives: [],
            assigned_medicines: [],
            assigned_services: [],
            account: null,
            service_debt: 0,
            insurance_policies: [],
            has_registration: false,
            registration_id: null,
            legal_entity_id: null,
            personal_cabinet_phone: null,
            ehealth_patient_id: null,
            client_ids: null,
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            contact_details: (value) => this.castToInstance(ContactDetails, value),
            cards: (value) => this.castToInstances(PatientCard, value),
            issued_discount_cards: (value) => this.castToInstances(IssuedDiscountCard, value),
            relatives: (value) => {
                if (_.isArray(value)) {
                    return this.castToInstances(Relative, value.map(v => ({
                        id: v.id,
                        full_name: [v.lastname, v.firstname, v.middlename].filter(_.isFilled).join(' '),
                        birthday: v.birthday,
                        relation: v.relation,
                        show_in_cabinet: v.show_in_cabinet,
                        show_in_cabinet_after_14: v.show_in_cabinet_after_14,
                        cabinet_deny_reason: v.cabinet_deny_reason,
                        proving_document_id: v.proving_document_id,
                    })));
                }
                return [];
            },
            clinics: (value) => this.castToArray(value),
            insurance_policies: (value) => this.castToInstances(InsurancePolicy, value),
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            firstname: required.and(maxlen(STRING_MAX_LEN)),
            lastname: maxlen(STRING_MAX_LEN),
            middlename: maxlen(STRING_MAX_LEN),
            passport_no: maxlen(20),
            location: required.and(maxlen(100)),
            street: maxlen(100),
            house_number: maxlen(50),
            apartment_number: maxlen(10),
            birthday: date.or(missing),
            comment: maxlen(TEXT_MAX_LEN),
            black_mark_comment: maxlen(TEXT_MAX_LEN),
            skk_comment: maxlen(TEXT_MAX_LEN),
            attention_comment: maxlen(TEXT_MAX_LEN),
            clinics: requiredArray.or(attributeEquals('status', 'guest')),
            source_id: required.or(attributeEquals('status', 'guest')),
            contact_details: (value) => this.validateSubmodel(value),
            insurance_policies: (value) => this.validateModelsArray(value),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients',
            fetch: '/api/v1/patients/{id}',
            update: '/api/v1/patients/{id}',
            updateQuestionnaire: '/api/v1/patients/questionnaire/{id}',
            getPrevClinicSpecAppointment: '/api/v1/patients/{id}/prev-appointment',
        }
    }

    /**
     * @inheritdoc
     */
    getDefaultMethods() {
        return {
            updateQuestionnaire : 'PUT',
            ...super.getDefaultMethods(),
        };
    }

    /**
     * Get patient cards with specializations
     *
     * @returns {array}
     */
    getCardsWithSpecializations() {
        let cards = [];
        this.cards.forEach((card) => {
            card.specializations.forEach((cardSpecialization) => {
                cardSpecialization.clinic_id = card.clinic_id;
                cardSpecialization.card_number = card.number;
                cards.push(cardSpecialization);
            });
        });
        return cards;
    }

    /**
     * Get patient archived cards with specializations
     *
     * @returns {array}
     */
    getArchivedCards() {
        let out = [];
        this.cards.map(card => {
            return card.archive_numbers.map(archivedCard => {
                out.push({
                    id: archivedCard.id,
                    number: archivedCard.number ,
                    specialization: archivedCard.specialization ,
                    clinic: {
                        id: card.clinic_id,
                        name: card.clinic_name
                    },
                    attachments: archivedCard.attachments,
                    attachments_data: archivedCard.attachments_data
                });
            })
        });

        return out;
    }


    /**
     * Get patient card by specialization
     *
     * @param {number} clinicId
     * @param {number} specializationId
     *
     * @returns {object}
     */
    getCard(clinicId, specializationId) {
        if (this.cards.length === 0) {
            return undefined;
        }
        return _.find(this.cards[0].specializations, (cardSpecialization) => cardSpecialization.specialization_id === specializationId);
    }

    /**
     * Get patient full name
     *
     * @returns {string}
     */
    get full_name() {
        return [
            this.lastname,
            this.firstname,
            this.middlename,
        ]
            .filter(_.isFilled)
            .join(' ');
    }


    /**
     * Get patient full name
     *
     * @returns {string}
     */
    get full_name_latin() {
        return [
            this.firstname_latin,
            this.lastname_latin,
        ]
            .filter(_.isFilled)
            .join(' ');
    }

    /**
     * Get patient debt boolean
     *
     * @returns {boolean}
     */
    get has_service_debt() {
        return !!this.service_debt;
    }

    /**
     * Set patient full name
     *
     * @param {string} val
     */
    set full_name(val) {
        let parts = val.split(' ').filter(p => p !== '');
        if (parts.length === 1) {
            this.firstname = parts[0];
        } else if (parts.length === 2) {
            this.lastname = parts[0];
            this.firstname = parts[1];
        } else {
            this.lastname = parts.shift();
            this.firstname = parts.shift();
            this.middlename = parts.join(' ');
        }
    }

    /**
     * Get patient full name
     *
     * @returns {string}
     */
    get full_address() {
        return [
            this.location,
            this.street ? __('ул.') + ' ' + this.street: null,
            this.house_number ? __('д.') + ' ' + this.house_number: null,
            this.apartment_number ? __('кв.') + ' ' + this.apartment_number: null,
        ]
            .filter(_.isFilled)
            .join(' ');
    }

    /**
     * Get patient age
     *
     * @returns {number}
     */
    get age() {
        if (this.birthday) {
            return moment().diff(this.birthday, 'years');
        }
        return 0;
    }

    /**
     * Get patient birdthdate
     *
     * @returns {number}
     */
    get birthdate() {
        if (this.birthday) {
            return moment(this.birthday).format('DD.MM.YYYY');
        }
        return '';
    }

    /**
     * Get patient cards specializations
     *
     * @returns {array}
     */
    get cardsSpecializations() {
        let specializations = [];
        this.cards.forEach((card) => {
            card.specializations.forEach((spec) => {
                let index = specializations.findIndex(item => item == spec.specialization_id);
                if (index === -1) {
                    specializations.push(spec.specialization_id);
                }
            });
        });
        return specializations;
    }

    /**
     * Get patient is patient
     *
     * @returns {bool}
     */
    get is_patient() {
        return this.status === CONSTANTS.PATIENT.STATUS.PATIENT;
    }

    /**
     * Get patient primary_phone_number
     *
     * @returns {mixed}
     */
    get primary_phone_number() {
        return this.contact_details.primary_phone_number;
    }

    /**
     * Get patient secondary_phone_number
     *
     * @returns {mixed}
     */
    get secondary_phone_number() {
        return this.contact_details.secondary_phone_number;
    }

    /**
     * Get patient card number
     *
     * @returns {number}
     */
    get card_number() {
        return this.cards.length === 0 ? undefined : this.cards[0].number;
    }

    /**
     * Get patient email
     *
     * @returns {mixed}
     */
    get email() {
        return this.contact_details.email;
    }

    /**
     * Get deposit account balance by clinic id
     *
     * @param {number} clinicId
     *
     * @returns {number}
     */
    getClinicDepositBalance(clinicId) {
        let clinicAccount = this.accounts
            ? this.accounts.find((account) => account.clinic_id == clinicId)
            : null;
        return numberFormat((clinicAccount ? clinicAccount.balance : 0));
    }

    /**
     * Get previous visit by clinic and specialization
     * @param {number} clinic
     * @param {number} specialization
     *
     * @returns {mixed}
     */
    getSpecializationPrevAppointment(clinic, specialization) {
        let method = this.getOption('methods.fetch');
        let route  = this.getRoute('getPrevClinicSpecAppointment');
        let params = this.getRouteParameters();
        let url    = this.getUrlWithQueryParams(route, params, {clinic, specialization});
        let data   = {};

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }

    /**
     * Update only questionnaire fields
     *
     */
    updateQuestionnaire() {
        let method = this.getUpdateMethod();
        let route  = this.getRoute('updateQuestionnaire');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data   = this._attributes;

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }
}

export default Patient;
