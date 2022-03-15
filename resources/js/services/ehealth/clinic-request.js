import BaseRequest from './base-request';
import AddressRequest from './address-request';
import Msp from '@/models/msp';
import Clinic from '@/models/clinic';
import CONSTANT from '@/constants';

import { 
    required,
    ukrSpelling,
} from '@/services/validation';

const KINDS = {
    'ambulant_clinic': 'AMBULANT_CLINIC',
    'clinic': 'CLINIC',
    'drugstore': 'DRUGSTORE',
    'drugstore_point': 'DRUGSTORE_POINT',
    'fap': 'FAP',
    'licensed_unit': 'LICENSED_UNIT',
};

class ClinicRequest extends BaseRequest
{
    constructor(clinic) {
        super(clinic);
        this._msp = null;
        if (clinic.status === CONSTANT.CLINIC.STATUS.INACTIVE) {
            this.addProp('status', () => false, [() => this.checkActiveServiceTypes()]);
        } else {
            this.addProp('name', () => clinic.official_name, [required, ukrSpelling], 'official_name');
            this.addProp('addresses', () => this.makeAddresses(clinic.address, clinic.reception_address));
            this.addProp('phones', () => this.makePhones({
                contact_phone: clinic.contact_phone, 
                additional_contact_phone: clinic.additional_contact_phone,
            }));
            this.addProp('email', () => clinic.email, [required]);
            this.addProp('working_hours', () => this.makeWorkingHours(clinic.working_hours));
            this.addProp('type', () => this.fromDict(clinic.kind, KINDS), [required, (v) => this.checkKind(v)], 'kind');
            this.addProp('legal_entity_id', () => this.makeLegalEntityId());
            this.addProp('location', () => this.makeLocation(clinic.lat, clinic.lng));
        }
    }

    makeAddresses(address, reception_address) {
        let request = new AddressRequest(address, 'RESIDENCE', true);
        return request.transform().then(() => {
            request.getErrors().forEach((err) => {
                this.addError(`address.${err.prop}`, err.error);
            });
            return this.getMsp().then((msp) => {
                if ((msp || {}).type === 'outpatient' && reception_address && _.isFilled(reception_address.address)) {
                    let rRequest = new AddressRequest(reception_address, 'RECEPTION', true);
                    return rRequest.transform().then(() => {
                        rRequest.getErrors().forEach((err) => {
                            this.addError(`reception_address.${err.prop}`, err.error);
                        });
                        return [request.getData(), rRequest.getData()];
                    });
                } else {
                    return [request.getData()];
                }
            });
        });
    }

    makeWorkingHours(hours) {
        if (_.isVoid(hours) || (typeof hours !== 'object') || hours.hours.length === 0) {
            return null;
        }
        let res = {};
        let keys = ['', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        hours.hours.map((hour) => {
            hour.days.forEach((day) => {
                let periods = [];
                if (_.isFilled(hour.break_from)) {
                    periods.push(
                        [this.formatHours(hour.from), this.formatHours(hour.break_from)], 
                        [this.formatHours(hour.break_to), this.formatHours(hour.to)]
                    );
                } else {
                    periods.push([this.formatHours(hour.from), this.formatHours(hour.to)]);
                }
                res[keys[day]] = periods;
            });
        });
        return res;
    }

    formatHours(h) {
        return h.replace(':', '.');
    }

    makeLocation(latitude, longitude) {
        if (_.isVoid(latitude) || _.isVoid(longitude)) {
            return null;
        }
        return {
            latitude: Number(latitude),
            longitude: Number(longitude),
        };
    }

    makeLegalEntityId() {
        return this.getMsp().then((msp) => {
            if (msp === null) {
                this.addError('msp_id', __('Пожалуйста, заполните это поле'));
                return null;
            }
            if (_.isVoid(msp.ehealth_id)) {
                this.addError('msp_id', __('Выбраный предоставитель мед. услуг не зарегистрирован в системе eHealth.'));
                return null;
            }
            return msp.ehealth_id;
        });
    }

    checkKind(v) {
        if (_.isVoid(v)) {
            return '';
        }
        return this.getMsp().then((msp) => {
            let allowed = ['LICENSED_UNIT'];
            switch ((msp || {}).type) {
                case 'pharmacy':
                    allowed = ['DRUGSTORE', 'DRUGSTORE_POINT'];
                    break;
                case 'primary_care':
                    allowed = ['CLINIC', 'AMBULANT_CLINIC', 'FAP'];
                    break;
            }
            if (allowed.indexOf(v) === -1) {
                return __('Данный тип подразделения не может быть добавлен к выбраному предоставителю мед. услуг');
            }
        });
    }

    getMsp() {
        if (this._msp === null) {
            if (_.isVoid(this._subject.msp_id)) {
                this._msp = Promise.resolve(null);
            } else {
                let msp = new Msp({id: this._subject.msp_id});
                this._msp = msp.fetch().then(() => msp);
            }
        }
        return this._msp;
    }

    checkActiveServiceTypes() {
        let clinic = new Clinic({id: this._subject.id});
        return clinic.fetch(['service_types']).then(() => {
            let hasActive = clinic.service_types.some((service) => service.is_active === 1);
            if (hasActive) {
                return __('Вам необходимо деактивировать виды услуг перед деактивацией клиники');
            }
        });
    }
}

export default ClinicRequest;