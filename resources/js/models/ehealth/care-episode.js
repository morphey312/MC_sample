import BaseModel from '@/models/base-model';
import {
    required,
    assertion,
    maxlen,
    ukrSpelling,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
} from '@/services/validation';

class CareEpisode extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            ehealth_id: null,
            name: null,
            patient_id: null,
            type: null,
            date_start: null,
            date_end: null,
            close_summary: null,
            status_reason: null,
            cancel_summary: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            name: required.and(maxlen(STRING_MAX_LEN).and(ukrSpelling)),
            type: required,
            date_start: required,
            date_end: required.or(assertion(() => !this.isClose)),
            status_reason: required.or(assertion(() => {
                return !(this.isClose || this.isCancel)
            })),
            close_summary: maxlen(TEXT_MAX_LEN).and(ukrSpelling),
            cancel_summary: maxlen(TEXT_MAX_LEN).and(ukrSpelling),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/ehealth/care-episodes',
            fetch: '/api/v1/ehealth/care-episodes/{id}',
            update: '/api/v1/ehealth/care-episodes/{id}',
            delete: '/api/v1/ehealth/care-episodes/{id}',
        }
    }
}

export default CareEpisode;
