import BaseModel from '@/models/base-model';

class Service extends BaseModel 
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            service_id: null,
            service_type: null,
            site_enquiry_id: null,
            cost: null,
            discount: null,
            payed_amount: null,
            appointment_id: null,
            refund_status: null,
            refunder_id: null,
            wait_list_record_id: null,
            is_prepayment: false
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            update: '/api/v1/site-enquiries/services/{id}',
        }
    }
}

export default Service;