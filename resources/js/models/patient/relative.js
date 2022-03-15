import BaseModel from '@/models/base-model';
import moment from 'moment';
import {
    required,
    maxlen,
    attributeEquals,
    STRING_MAX_LEN, assertion,
} from '@/services/validation';

const THRESHOLD_AGE = 14;

class Relative extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            full_name: null,
            birthday: null,
            relation: null,
            show_in_cabinet: false,
            show_in_cabinet_after_14: false,
            cabinet_deny_reason: null,
            proving_document_id: null,
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            full_name: required,
            relation: required.and(maxlen(STRING_MAX_LEN)),
            proving_document_id: required.or(assertion(() => {
                if (this.show_in_cabinet_after_14 == true || this.show_in_cabinet == true){
                    return false;
                }else {
                    return true;
                }
            })),
        };
    }

    /**
     * Get relative age
     *
     * @returns {number}
     */
    get age() {
        return _.isFilled(this.birthday) ?
            moment().diff(this.birthday, 'years')
            : 0;
    }

    /**
     * Check if relative is 14 yrs old
     *
     * @returns {bool}
     */
    get has_14() {
        return this.age >= THRESHOLD_AGE;
    }

    /**
     * Check if relative is exposed in personal cabinet
     *
     * @returns {bool}
     */
    get is_granted() {
        return this.has_14 ? this.show_in_cabinet_after_14 : this.show_in_cabinet;
    }
}

export default Relative;
