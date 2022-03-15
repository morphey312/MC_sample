import BaseModel from '@/models/base-model';
import Patient from '@/models/patient';

class PatientUser extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Patient_User';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            phone: null,
            patient_id: null,
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            patient: (value) => this.castToInstance(Patient, value, true),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/users',
            update: '/api/v1/patients/users/{id}',
            delete: '/api/v1/patients/users/{id}',
            passwordReset: '/api/v1/patients/users/{id}/password-reset',
        }
    }

    /**
     * Reset patient password
     *
     * @returns {Promise}
     */
    passwordReset(data) {
        let method = 'POST';
        let route = this.getRoute('passwordReset');
        let url = this.getURL(route, {id: data.id});

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }
}

export default PatientUser;
