import BaseRequest from './base-request';
import { 
    required,
    ukrSpelling,
} from '@/services/validation';
import EmployeeSpecialityTypeRepository from '@/repositories/employee/speciality-type';
import Msp from '@/models/msp';
import moment from 'moment';

const CONDITIONS = {
    'field': 'FIELD',
    'inpatient': 'INPATIENT',
    'outpatient': 'OUTPATIENT',
}

class ServiceTypeRequest extends BaseRequest
{
    constructor(service, clinic) {
        super(service);
        this._clinic = clinic;
        if (_.isVoid(service.ehealth_id)) {
            this.addProp('division_id', () => clinic.ehealth_id);
            this.addProp('speciality_type', () => this.makeSpeciality(service.speciality_type_id), [required], 'speciality_type_id');
            this.addProp('providing_condition', () => this.fromDict(service.providing_condition, CONDITIONS), [required, (v) => this.checkProvidingConditions(v)]);
        }
        this.addProp('comment', () => service.comment, [ukrSpelling]);
        this.addProp('available_time', () => this.makeAvailableTime(service.available_time));
        this.addProp('not_available', () => this.makeNotAvailableTime(service.not_available));
    }

    makeSpeciality(typeId) {
        let repository = new EmployeeSpecialityTypeRepository();
        return repository.fetchList().then((list) => {
            let type = _.findById(list, typeId);
            return type === undefined ? null : type.code;
        });
    }

    checkProvidingConditions(val) {
        let msp = new Msp({id: this._clinic.msp_id});
        return msp.fetch().then(() => {
            if (!this.isValidCondition(val, msp.type)) {
                return __('Данные условия предоставления услуг не могут быть использованы совместно с текущим типом предоставителя мед. услуг');
            }
        });
    }

    makeAvailableTime(time) {
        if (time) {
            let days = ['', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            return time.hours.map((hour) => {
                return {
                    days_of_week: hour.days.map((day) => days[day]),
                    all_day: false,
                    available_start_time: this.formatAvailableHours(hour.from),
                    available_end_time: this.formatAvailableHours(hour.to),
                }
            });
        }
        return null;
    }

    formatAvailableHours(h) {
        return `${h}:00`;
    }

    makeNotAvailableTime(time) {
        if (time && time.length !== 0) {
            return time.map((period) => {
                return _.onlyFilled({
                    description: period.comment,
                    during: {
                        start: this.formatUnavaliableTime(period.date_from, period.time_from),
                        end: this.formatUnavaliableTime(period.date_to, period.time_to),
                    }
                });
            });
        }
        return null;
    }

    formatUnavaliableTime(date, time) {
        return moment(`${date} ${time}:00`).toISOString();
    }

    isValidCondition(cond, type) {
        let valid = {
            'emergency': ['FIELD'],
            'outpatient': ['INPATIENT', 'OUTPATIENT'],
            'primary_care': ['OUTPATIENT'],
        }
        return valid[type] !== undefined && valid[type].indexOf(cond) !== -1;
    }

    getData() {
        if (Object.keys(this._data).length === 0) {
            return {comment: ''};
        }
        return this._data;
    }
}

export default ServiceTypeRequest;