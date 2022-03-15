import BaseModel from '@/models/base-model';
import Place from '@/models/department/room/place';
import Occupation from '@/models/department/room/occupation';
import {
    maxlen,
    required,
    STRING_MAX_LEN,
} from '@/services/validation';

class Room extends BaseModel {

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            name: '',
            status: 1,
            department_id: null,
            places: [],
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN)),
            department_id: required,
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            places: (value) => _.isArray(value) ? value.map((place) => this.initSubModel(Place, place)) : [],
            occupations: (value) => _.isArray(value) ? value.map((item) => this.initSubModel(Occupation, item)) : [],
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/departments/rooms',
            fetch: '/api/v1/departments/rooms/{id}',
            update: '/api/v1/departments/rooms/{id}',
            delete: '/api/v1/departments/rooms/{id}',
        }
    }
}

export default Room;