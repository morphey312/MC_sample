import BaseRequest from './base-request';
import ServiceType from '@/models/clinic/service-type';
import Employee from '@/models/employee';

class EmployeeServiceTypeRequest extends BaseRequest
{
    constructor(service, employee) {
        super(service);
        this._employee = employee;
        this.addProp('healthcare_service_id', () => this.makeServiceId(service));
        this.addProp('employee_id', () => employee.ehealth_id);
    }

    makeServiceId(service) {
        let employee = new Employee({id: this._employee.id});
        return employee.fetch(['specialities']).then(() => {
            let serviceType = new ServiceType({id: service.service_id});
            return serviceType.fetch().then(() => {
                let speciality = _.find(employee.employee_specialities, (spec) => spec.speciality_id === serviceType.speciality_type_id);
                if (speciality === undefined || speciality.primary === false) {
                    this.addError('service_id', __('Данная специализация не является рабочей специализацией врача'));
                    return null;
                }
                return serviceType.ehealth_id;
            });
        });
    }
}

export default EmployeeServiceTypeRequest;