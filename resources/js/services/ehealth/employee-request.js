import BaseRequest from './base-request';
import BaseEmployeeRequest from './base-employee-request';
import DoctorRequest from './doctor-request';
import Employee from '@/models/employee';
import {
    required,
    email,
    numeric,
    missing,
    ukrSpelling,
    not,
} from '@/services/validation';
import CONSTANT from '@/constants';

class EmployeeProfileRequest extends BaseEmployeeRequest
{
    constructor(employee) {
        super(employee, true);
        this.addProp('no_tax_id', () => employee.no_tax_id);
        this.addProp('tax_id', () => employee.tax_id, [required]);
        this.addProp('email', () => employee.email, [required, email]);
        this.addProp('working_experience', () => employee.experience, [numeric.or(missing)], 'experience');
        this.addProp('about_myself', () => employee.about, [ukrSpelling], 'about');
    }
}

class EmployeeRequest extends BaseRequest
{
    constructor(employee) {
        super(employee);
        this._relatedData = null;
        this.addProp('employee_id', () => employee.ehealth_id);
        this.addProp('division_id', () => null);
        this.addProp('legal_entity_id', () => null);
        this.addProp('position', () => this.makePosition(), [required], 'clinic.position');
        this.addProp('start_date', () => this.makeStartDate(), [required], 'clinic.start_date');
        this.addProp('end_date', () => this.makeEndDate());
        this.addProp('status', () => this.makeStatus());
        this.addProp('employee_type', () => this.makeEmployeeType(), [required, not('OWNER')], 'clinic.employee_type');
        this.addProp('party', () => this.makeProfile(employee));
        this.addProp('doctor', () => this.makeDoctorRequest(employee));
    }

    makeProfile(employee) {
        return this.fetchRelatedData().then(() => {
            let request = new EmployeeProfileRequest(employee);
            return request.transform().then(() => {
                request.getErrors().forEach((err) => {
                    this.addError(err.prop, err.error);
                });
                return request.getData();
            });
        });
    }

    makeDoctorRequest(employee) {
        return this.fromMainClinic((clinic) => {
            let property;
            let type = clinic.ehealth_employee_type;
            if (type === CONSTANT.EHEALTH.EMPLOYEE_TYPE.DOCTOR) {
                property = 'doctor';
            } else if (type === CONSTANT.EHEALTH.EMPLOYEE_TYPE.SPECIALIST) {
                property = 'specialist';
            } else if (type === CONSTANT.EHEALTH.EMPLOYEE_TYPE.PHARMACIST) {
                property = 'pharmacist';
            } else {
                return null;
            }
            let request = new DoctorRequest(employee, type);
            return request.transform().then(() => {
                request.getErrors().forEach((err) => {
                    this.addError(`doctor.${err.prop}`, err.error);
                });
                let result = {};
                result[property] = request.getData();
                return result;
            });
        });
    }

    fetchRelatedData() {
        if (this._relatedData === null) {
            let model = new Employee({id: this._subject.id});
            this._relatedData = model.fetch([
                'msp', // TODO: this scope no longer exists
                'documents',
                'clinics.position',
                'educations.country',
                'qualifications',
                'specialities',
                'science_degrees',
            ]).then(() => {
                if (model.employee_clinics.length === 0) {
                    this.addError('clinic', __('Не выбрана основная клиника'));
                }
                this._subject.employee_clinics = model.employee_clinics;
                this._subject.employee_documents = model.employee_documents;
                this._subject.employee_educations = model.employee_educations;
                this._subject.employee_qualifications = model.employee_qualifications;
                this._subject.employee_specialities = model.employee_specialities;
                this._subject.employee_science_degrees = model.employee_science_degrees;
            });
        }
        return this._relatedData;
    }

    fromMainClinic(callback, fallback = '') {
        return this.fetchRelatedData().then(() => {
            if (this._subject.employee_clinics.length === 0) {
                return fallback;
            }
            return callback(this._subject.employee_clinics[0]);
        });
    }

    makePosition() {
        return this.fromMainClinic((clinic) => clinic.ehealth_position);
    }

    makeStartDate() {
        return this.fromMainClinic((clinic) => clinic.date_start_working);
    }

    makeEndDate() {
        return this.fromMainClinic((clinic) => clinic.date_end_working);
    }

    makeEmployeeType() {
        return this.fromMainClinic((clinic) => clinic.ehealth_employee_type);
    }

    makeStatus() {
        return this.fromMainClinic((clinic) => {
            return ['not_working', 'removed'].indexOf(clinic.status) === -1 ? 'NEW' : 'DISMISSED';
        });
    }

    getData() {
        let data = {
            ...this._data,
        };
        if (data.doctor !== undefined) {
            let doctorData = data.doctor;
            delete data.doctor;
            data = {
                ...data,
                ...doctorData,
            };
        }
        return {
            employee_request: data,
        };
    }
}

export default EmployeeRequest;
