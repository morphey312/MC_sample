import BaseModel from '@/models/base-model';
import moment from 'moment';
import {
    required,
    date,
    greaterThanAttribute,
    gte,
    assertion,
} from '@/services/validation';

/**
 * AppointmentStatus model
 */
class AppointmentLimitation extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Appointment_Limitation';
    }
    
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            clinic_id: null,
            specialization_id: null,
            limitation: 10,
            date_from: '',
            date_to: '',
            doctors: [],
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            date_from: (value) => {
                if (value.length === 0) {
                    return moment().add(1, 'month').startOf('month').format('YYYY-MM-DD');
                }

                return value;
            },
            date_to: (value) => {
                if (value.length === 0) {
                    return moment().add(1, 'month').endOf('month').format('YYYY-MM-DD');
                }

                return value;
            }
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            clinic_id: required,
            specialization_id: required,
            date_to: required.and(greaterThanAttribute('date_from').and(date)),
            date_from: required.and(date),
            limitation: required.and(gte(0)),
            doctors: assertion(() => {
                if (_.isEmpty(this.doctors)){
                    return true;
                }

                let totalPercents = 0;
                this.doctors.forEach((doctor) => {
                    totalPercents += +doctor.limitation_percent;
                });
                
                return totalPercents <= 100;
            }),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/appointments/limitations',
            fetch: '/api/v1/appointments/limitations/{id}',
            update: '/api/v1/appointments/limitations/{id}',
            delete: '/api/v1/appointments/limitations/{id}',
            updateDoctors: '/api/v1/appointments/limitations/updateDoctors/{id}',
        }
    }

    /**
     * @inheritdoc
     */
    options() {
        return {
            methods: {
                updateDoctors: 'POST',
            }
        }
    }

    /**
     * Attach doctor
     *
     * @returns Promise
     */
    updateDoctors(doctors) {
        let method = this.getOption('methods.updateDoctors');
        let route  = this.getRoute('updateDoctors');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data   = {
            id: this.id,
            doctors
        };

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }
}

export default AppointmentLimitation;