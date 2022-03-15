import BaseModel from '@/models/base-model';


/**
 * Check model
 */
class Check extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'Check';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            body: null,
            amount: null,
            cashbox_id: null,
            employee_id: null,
            money_reciever_cashbox_id: null,
            type: null,
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/checkbox/checks',
            fetch: '/api/v1/checkbox/checks/{id}',
            update: '/api/v1/checkbox/checks/{id}',
            delete: '/api/v1/checkbox/checks/{id}',
        }
    }

}

export default Check;
