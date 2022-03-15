import BaseRequest from './base-request';
import DocumentRequest from './document-request';
import {
    required,
    ukrSpelling,
} from '@/services/validation';

const GENDERS = {
    'male': 'MALE',
    'female': 'FEMALE',
};

class BaseEmployeeRequest extends BaseRequest
{
    constructor(employee, documentsRequired = false) {
        super(employee);
        this._documentsRequired = documentsRequired;
        this.addProp('first_name', () => employee.first_name, [required, ukrSpelling]);
        this.addProp('last_name', () => employee.last_name, [required, ukrSpelling]);
        this.addProp('second_name', () => employee.middle_name, [ukrSpelling], 'middle_name');
        this.addProp('birth_date', () => employee.birth_date, [required]);
        this.addProp('gender', () => this.fromDict(employee.gender, GENDERS), [required]);
        this.addProp('phones', () => this.makePhones({
            phone: employee.phone, 
            additional_phone: employee.additional_phone,
        }));
        this.addProp('documents', () => this.makeDocuments(employee.employee_documents));
    }

    makeDocuments(documents) {
        if (documents.length === 0) {
            if (this._documentsRequired) {
                this.addError('documents', __('Необходимо заполнить раздел документов'));
            }
            return null;
        }
        return Promise.all(documents.map((doc, index) => {
            let request = new DocumentRequest(doc);
            return request.transform().then(() => {
                request.getErrors().forEach((err) => {
                    this.addError(`documents.${index}.${err.prop}`, err.error);
                });
                return request.getData();
            });
        }));
    }
}

export default BaseEmployeeRequest;