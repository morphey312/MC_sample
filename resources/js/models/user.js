import BaseModel from '@/models/base-model';
import CONSTANTS from '@/constants';

import {
    required,
    requiredArray,
    password,
    maxlen,
    missing,
    modelExists,
    STRING_MAX_LEN
} from '@/services/validation';

const notWorkingStatuses = [CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING, CONSTANTS.EMPLOYEE.STATUSES.REMOVED];

class User extends BaseModel
{
    defaults() {
        return {
            id: null,
            login: null,
            unread_notifications: 0,
            password: null,
            roles: [],
            permissions: [],
            locales: {},
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            login: required.and(maxlen(STRING_MAX_LEN)),
            password: [
                required.or(modelExists),
                password.or(missing),
            ],
            roles: requiredArray,
        };
    }

    /**
     * Serialize user model
     *
     * @returns {object}
     */
    serialize() {
        return {
            id: this.id,
            login: this.login,
            unread_notifications: this.unread_notifications,
            employee: this.employee,
            locales: this.locales,
        };
    }

    /**
     * Get list of clinic IDs
     *
     * @returns {array}
     */
    get workingClinics() {
        return _.get(this, 'employee.clinics', [])
            .filter((clinic) => this.isNotWorkingStatus(clinic.status))
            .map((clinic) => clinic.id);
    }

    /**
     * Get list of clinic IDs
     *
     * @returns {array}
     */
    get clinics() {
        return _.get(this, 'employee.clinics', [])
            .map((clinic) => clinic.id);
    }

    /**
     * Get employee_id
     *
     * @returns {number}
     */
    get employee_id() {
        return _.get(this, 'employee.id');
    }

    /**
     * Get full name
     *
     * @returns {string}
     */
    get full_name() {
        return _.get(this, 'employee.full_name');
    }

    /**
     * Check if user is doctor
     *
     * @returns {bool}
     */
    get isDoctor() {
        return _.get(this, 'employee.clinics', [])
            .filter((clinic) => clinic.is_doctor).length !== 0;
    }

    /**
     * Check if user is operator
     *
     * @returns {bool}
     */
    get isOperator() {
        return _.get(this, 'employee.clinics', [])
            .filter((clinic) => clinic.is_operator).length !== 0;
    }

    /**
     * Check if user is cashier
     *
     * @returns {bool}
     */
    get isCashier() {
        return _.get(this, 'employee.clinics', [])
            .filter((clinic) => clinic.is_cashier == true).length !== 0;
    }

    /**
     * Check if user has VoIP
     *
     * @returns {bool}
     */
    get hasVoIP() {
        return _.get(this, 'employee.clinics', [])
            .filter((clinic) => clinic.has_voip == true).length !== 0;
    }

    /**
     * Check if user is reception
     *
     * @returns {bool}
     */
    get isReception() {
        return _.get(this, 'employee.clinics', [])
            .filter((clinic) => clinic.is_reception == true).length !== 0;
    }

    /**
     * Get employee specialization IDs
     *
     * @returns {array}
     */
    get specializations() {
        let list = [];
        _.get(this, 'employee.clinics', []).forEach((clinic) => {
            clinic.specializations.forEach((specialization) => {
                if (list.indexOf(specialization.id) === -1) {
                    list.push(specialization.id);
                }
            });
        });
        return list;
    }

    /**
     * Get cashier clinic_id
     *
     * @returns {bool}
     */
    get cashierClinicId() {
        let clinic = _.get(this, 'employee.clinics', [])
                 .find((clinic) => clinic.is_cashier);
        return clinic ? clinic.id : null;
    }

    /**
     * Get user primary clinic_id
     *
     * @returns {bool}
     */
    get primaryClinicId() {
        let clinic = _.get(this, 'employee.clinics', [])
                 .find((clinic) => clinic.is_primary);
        return clinic ? clinic.id : null;
    }

    /**
     * Check if senrty is enabled for this user
     *
     * @returns {bool}
     */
    get hasSentry() {
        return _.get(this, 'employee.has_sentry') == 1;
    }

    /**
     * Check if user has HR portal account
     *
     * @returns {bool}
     */
    get hasHrPortalAccount() {
        return _.get(this, 'employee.has_portal_account') == 1;
    }

    /**
     * Get system_status
     *
     * @returns {string}
     */
    get system_status() {
        return _.get(this, 'employee.system_status');
    }

    /**
     * Check if user has unread notification
     *
     * @returns {bool}
     */
     get hasUnreadNotification() {
        return _.get(this, 'unread_notifications') > 0;
    }

    /**
     * Check if the given status is working status
     *
     * @param {String} status
     *
     * @returns {Bool}
     */
    isNotWorkingStatus(status) {
        return notWorkingStatuses.indexOf(status) === -1;
    }

    /**
     * Get language by suffix
     *
     * @param {String} suffix
     *
     * @returns {String}
     */
    langBySuffix(suffix) {
        let locales = this.locales;
        for (let lang in locales) {
            if (locales[lang].suffix === suffix) {
                return {
                    ...locales[lang],
                    code: lang,
                };
            }
        }
        return null;
    }
}

export default User;
