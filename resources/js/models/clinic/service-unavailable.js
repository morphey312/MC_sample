import BaseModel from '@/models/base-model';
import {
    maxlen,
    required,
    TEXT_MAX_LEN,
} from '@/services/validation';
import moment from 'moment';

class ServiceUnavailable extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            date_from: null,
            time_from: '00:00',
            date_to: null,
            time_to: '00:00',
            comment: null,
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            date_from: required,
            time_from: required,
            date_to: required.and(() => {
                let from = moment(`${this.date_from} ${this.time_from}:00`);
                let to = moment(`${this.date_to} ${this.time_to}:00`);
                if (!to.isAfter(from)) {
                    return __('Дата окончания должна быть позже даты начала');
                }
            }),
            time_to: required,
            comment: required.and(maxlen(TEXT_MAX_LEN)),
        };
    }
}

export default ServiceUnavailable;