import BaseModel from '@/models/base-model';

/**
 * ServiceItem model
 */
class ServiceItem extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            appointment_service_id: null,
            service_id: null,
            service_type: null,
            quantity: 1,
            cost: 0,
            self_cost: 0,
            discount: 0,
        }
    }
}

export default ServiceItem;