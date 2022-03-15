<template>
    <act-form
        :model="model"
        :is-analysis="isAnalysis">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Создать') }}
            </el-button>
        </div>
    </act-form>
</template>

<script>
import ActForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import PriceAgreementAct from "@/models/price-agreement-act";
import CONSTANTS from "@/constants";

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ActForm,
    },
    props: {
        rows: {
            type: Array,
            required: true
        },
        serviceType: {
            type: String,
            required: true
        },
    },
    computed: {
        isAnalysis() {
            return this.serviceType === CONSTANTS.PRICE.SERVICE_TYPE.ANALYSIS;
        },
    },
    beforeMount() {
        this.rows.forEach(row => {
            let price = {}

            if (this.isAnalysis) {
                price.service_name = row.service.name;
                price.laboratory_name = row.service.laboratory_name;
                price.laboratory_code = row.service.laboratory_code;
            } else {
                price.name = row.service.name
                price.specialization_name = row.service.specialization_name;
            }

            price.service_type = this.serviceType;
            price.service_id = row.price.service_id;
            price.change_type = row.price.change_type;
            price.price_id = row.price.id;
            price.currency = CONSTANTS.CURRENCY.UAH;
            price.recommended_cost = row.price._recommended_cost;
            if (row.price._change_type === null && row.price._recommended_cost > 0) {
                price.change_type = CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CHANGE_COST;
            } else {
                 price.change_type = row.price._change_type;
            }
            price.cost = row.price.cost;
            price.price_date_from = row.price.price_date_from;
            price.price_date_to = row.price.price_date_to;
            if (row.price._change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.ADD_CLINIC) {
                price.clinics = row.service.clinics.map((clinic) => {
                    return {
                        clinic_id: clinic.id,
                        code: clinic.code,
                        duration_days: clinic.duration_days,
                    }
                });
            } else {
                let clinics = row.price.clinics.map((id) => {return {clinic_id: id}});
                price.clinics = clinics;
            }
            price.clinics_for_show = row.price.clinics;
            this.model.act_prices.push(price);
        })
    },
    data() {
        return {
            model: new PriceAgreementAct({
                status: CONSTANTS.PRICE_AGREEMENT_ACT.STATUSES.IN_WORK,
                type: this.serviceType,
                employee_id: this.$store.state.user.employee_id,
            }),
        }
    },
}
</script>
