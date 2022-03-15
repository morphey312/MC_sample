import BaseModel from '@/models/base-model';
import {
    required,
    requiredArray,
} from '@/services/validation';

/**
 * TimeSheet model
 */
class TimeSheet extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            time_from: '',
            time_to: '',
            specializations: [],
            specialization_data: {},
        }
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            time_from: (value) => value.substring(0, 5),
            time_to: (value) => value.substring(0, 5),
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            specializations: requiredArray,
            time_from: required,
            time_to: required,
        };
    }

    castSpecializationData(specializations) {
        _.each(specializations, (item) => {
            this.specialization_data[item.id] = { workspace_id: (item.workspace_id ? item.workspace_id : null) };
        });
    }
}

export default TimeSheet;