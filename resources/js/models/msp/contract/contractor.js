import BaseModel from '@/models/base-model';
import moment from 'moment';
import {
    required,
    requiredArray,
    maxlen,
    STRING_MAX_LEN
} from '@/services/validation';

/**
 * MspArchive model
 */
class MspSubcontractor extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'MspContractContractor';
    }

    /** 
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            ehealth_id: null,
            type: null,
	        name: null,
	        edrpou: null,
	        contract_number: null,
	        issued_at: null,
	        expires_at: null,
	        clinics: [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            contract_number: required.and(maxlen(STRING_MAX_LEN)),
            issued_at: required,
            expires_at: required.and(() => {
                if (!moment(this.expires_at).isAfter(this.issued_at)) {
                    return __('Дата окончания должна быть позже даты начала');
                }
            }),
            clinics: requiredArray.and((clinics) => {
                if (clinics.some((clinic) => _.isVoid(clinic.service))) {
                    return __('Выберите услуги всем клиникам');
                }
            }),
        };
    }
}

export default MspSubcontractor;