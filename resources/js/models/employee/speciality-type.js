import BaseModel from '@/models/base-model';

class EmployeeSpecialityType extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            name: null,
        };
    }
}

export default EmployeeSpecialityType;