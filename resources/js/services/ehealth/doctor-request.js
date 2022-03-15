import BaseRequest from './base-request';
import EducationRequest from './education-request';
import QualificationRequest from './qualification-request';
import SpecialityRequest from './speciality-request';
import ScienceDegreeRequest from './science-degree-request';
import EmployeeSpecialityTypeRepository from '@/repositories/employee/speciality-type';
import CONSTANT from '@/constants';

class DoctorRequest extends BaseRequest
{
    constructor(employee, type) {
        super(employee);
        this.addProp('educations', () => this.makeEducations(employee.employee_educations));
        this.addProp('qualifications', () => this.makeQualifications(employee.employee_qualifications));
        this.addProp('specialities', () => this.makeSpecialities(employee.employee_specialities, type));
        this.addProp('science_degree', () => this.makeScienceDegree(employee.employee_science_degrees));
    }

    makeEducations(educations) {
        if (educations.length === 0) {
            this.addError('educations', __('Необходимо заполнить раздел образования'));
            return null;
        }
        return Promise.all(educations.map((education, index) => {
            let request = new EducationRequest(education);
            return request.transform().then(() => {
                request.getErrors().forEach((err) => {
                    this.addError(`educations.${index}.${err.prop}`, err.error);
                });
                return request.getData();
            });
        }));
    }

    makeQualifications(qualifications) {
        if (qualifications.length === 0) {
            return null;
        }
        return Promise.all(qualifications.map((qualification, index) => {
            let request = new QualificationRequest(qualification);
            return request.transform().then(() => {
                request.getErrors().forEach((err) => {
                    this.addError(`qualifications.${index}.${err.prop}`, err.error);
                });
                return request.getData();
            });
        }));
    }

    makeSpecialities(specialities, type) {
        if (specialities.length === 0) {
            this.addError('specialities', __('Необходимо заполнить раздел специальностей'));
            return null;
        }
        let hasPrimary = specialities.some((spec) => spec.primary);
        if (!hasPrimary) {
            this.addError('specialities', __('Укажите основную рабочую специализацию'));
        }
        let repository = new EmployeeSpecialityTypeRepository();
        return repository.fetchList().then((mapping) => {
            return Promise.all(specialities.map((speciality, index) => {
                let request = new SpecialityRequest(speciality);
                return request.transform().then(() => {
                    request.getErrors().forEach((err) => {
                        this.addError(`specialities.${index}.${err.prop}`, err.error);
                    });
                    if (!this.isSpecialityValid(speciality.speciality_code, type, mapping)) {
                        this.addError(`specialities`, __('Специализация {speciality} не может быть использована для данного типа сотрудника', {speciality: speciality.speciality_name}));
                    }
                    return request.getData();
                });
            }));
        });
    }

    isSpecialityValid(speciality, type, mapping) {
        let rule = _.find(mapping, i => i.code === speciality);
        if (!rule) {
            return false;
        }
        switch (type) {
            case CONSTANT.EHEALTH.EMPLOYEE_TYPE.DOCTOR:
                return rule.for_doctor;
            case CONSTANT.EHEALTH.EMPLOYEE_TYPE.SPECIALIST:
                return rule.for_specialist;
            case CONSTANT.EHEALTH.EMPLOYEE_TYPE.PHARMACIST:
                return rule.for_pharmacist;
            case CONSTANT.EHEALTH.EMPLOYEE_TYPE.ASSISTANT:
                return rule.for_assistant;
        }
        return false;
    }

    makeScienceDegree(degrees) {
        if (degrees.length === 0) {
            return null;
        }
        let degree = degrees[0];
        let request = new ScienceDegreeRequest(degree);
        return request.transform().then(() => {
            request.getErrors().forEach((err) => {
                this.addError(`science_degree.${err.prop}`, err.error);
            });
            return request.getData();
        });
    }
}

export default DoctorRequest;
