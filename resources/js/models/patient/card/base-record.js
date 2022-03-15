import Employee from '@/models/employee';
import BaseModel from '@/models/base-model';

class BaseRecord extends BaseModel
{
    /**
     * @inheritdoc
     */
    assign(attributes) {
        if (attributes.recordable !== undefined) {
            let recordable = attributes.recordable;
            delete attributes.recordable;
            attributes = {
                ...attributes,
                ...recordable,
            };
        }
        super.assign(attributes);
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            doctor: (value) => this.castToInstance(Employee, value, true),
        };
    }
}

export default BaseRecord;