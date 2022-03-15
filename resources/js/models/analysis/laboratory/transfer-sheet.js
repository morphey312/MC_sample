import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';

/**
 * Laboratory order model
 */
class TransferSheet extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            external_transfer_id: null,
            clinic_id: null,
            status: null,
            send_time: null,
            reciever_id: null,
            reciever_name: null,
            courier_id: null,
            courier_name: null,
            laboratory_id: null,
            barcode: null,
            sender_comment: null,
            reciever_comment: null,
            containers: [],
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            clinic_id: required,
            reciever_id: required,
            courier_id: required,
            laboratory_id: required,
            barcode: required.and(maxlen(STRING_MAX_LEN)),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/analysis/laboratories/transfer-sheets',
            update: '/api/v1/analysis/laboratories/transfer-sheets/{id}',
            status: '/api/v1/analysis/laboratories/transfer-sheets/{id}/change-status',
        }
    }


    /**
     * Change transfer sheet status
     *
     * @param {object} data
     *
     * @returns {Promise}
     */
     changeStatus(status) {
        let route  = this.getRoute('status');
        let params = this.getRouteParameters();
        let url    = this.getURL(route, params);

        let config = () => ({
            url,
            method: this.getSaveMethod(),
            data: {status},
            params: this.getSaveQuery(),
            headers: this.getSaveHeaders(),
        });

        return this.request(
            config,
            this.onSave,
            this.onSaveSuccess,
            this.onSaveFailure
        );
    }
}

export default TransferSheet;
