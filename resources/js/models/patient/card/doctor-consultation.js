import BaseRecord from './base-record';
import {
    required,
    maxlen,
    TEXT_MAX_LEN
} from '@/services/validation';

class ConsultationRecord extends BaseRecord
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            consultation_record_id: null,
            specialization_id: null,
            specialization_name: null,
            comment: null,
            outclinic_specialization_id: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            specialization_name: required.and(maxlen(TEXT_MAX_LEN)),
        };
    }
}

export default ConsultationRecord;
