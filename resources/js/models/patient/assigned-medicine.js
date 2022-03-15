import BaseModel from '@/models/base-model';
import {
    required,
    maxlen,
    STRING_MAX_LEN,
} from '@/services/validation';
import CONSTANTS from '@/constants';

class AssignedMedicine extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            assigner_id: null,
            patient_id: null,
            medicine_id: null,
            medicine_type: null,
            clinic_id: null,
            card_specialization_id: null,
            card_assignment_id: null,
            appointment_id: null,
            medication_duration: 1,
            base_cost: 0,
            cost: 0,
            self_cost: 0,
            quantity: 1,
            is_free: 1,
            issued_quantity: 0,
            comment: null,
            by_policy: false,
            franchise: 0,
            warranter: '',
            is_apteka24: false,
            apteka24_id: null,
            apteka24_order_id: null,
            store_rests: [],
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            cost: (val) => (this.medicine_type == CONSTANTS.ASSIGNED_MEDICINE.TYPES.OUTCLINIC_MEDICINE) ? __('Из аптек') : val,
            quantity: (val) => Number(val),
        }
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            assigner_id: required,
            patient_id: required,
            clinic_id: required,
            medicine_id: required,
            medicine_type: required.and(maxlen(STRING_MAX_LEN)),
            is_free: required,
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            delete: '/api/v1/patients/assigned-medicines/{id}',
        }
    }

    /**
     * Cast entity data
     *
     * @param {object} row
     * @param {cbject} filters
     *
     * @return {object}
     */
    createAssignedMedicine(row, filters) {
        let cost = 0;
        let self_cost = 0;
        let base_cost = 0;
        let rests = 0;
        let clinic = filters.clinic;

        if (row.store_rests.length !== 0) {
            let stores = row.store_rests.filter((item) => {
                return item.clinic.id == clinic || (Array.isArray(clinic) && (clinic.indexOf(item.clinic.id) != -1));
            });

            if (stores.length !== 0) {
                cost = stores[0].cost;
                base_cost = stores[0].cost;
                self_cost = stores[0].self_cost;
                stores.forEach(item => rests += Number(item.rest));
            }
        }

        return {
            id: row.id,
            medicine_id: row.id,
            name: row.name,
            self_cost,
            base_cost,
            cost,
            rests,
        };
    }
}

export default AssignedMedicine;
