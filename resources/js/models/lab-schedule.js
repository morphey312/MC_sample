import BaseModel from '@/models/base-model';
import {
    date,
} from '@/services/validation';

class LabSchedule extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            date: null,
            dates: []
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/lab-schedule',
            fetch: '/api/v1/lab-schedule/{id}',
            update: '/api/v1/lab-schedule/{id}',
            delete: '/api/v1/lab-schedule',
        }
    }

    /**
     * Delete day-offs
     *
     * @param {array} dates
     *
     * @return Promise
     */
    deleteDates(dates) {
        let method = this.getOption('methods.delete');
        let route  = this.getRoute('delete');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);
        let data   = {
            dates: dates,
        };

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response.response.data);
        });
    }
}

export default LabSchedule;
