import BaseRecord from './base-record';
import Employee from '@/models/employee';
import {
    required,
    requiredArray,
} from '@/services/validation';

class Document extends BaseRecord
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            card_specialization_id: null,
            appointment_id: null,
            patient_id: null,
            name: null,
            doctor_id: null,
            is_questionnaire: false,
            attachments: [],
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            patient_id: required,
            attachments: requiredArray,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/documents',
            update: '/api/v1/patients/cards/documents/{id}',
            delete: '/api/v1/patients/cards/documents/{id}',
        };
    }

    assign(attributes) {
        let oldAttributes = attributes;
        if (attributes.recordable !== undefined) {
            let recordable = attributes.recordable;
            delete attributes.recordable;
            attributes = {
                ...attributes,
                ...recordable,
            };
            attributes.id = oldAttributes.id;
        }
        super.assign(attributes);
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            doctor: (value) => this.castToInstance(Employee, value, true),
        };
    }
}

export default Document;
