<template>
    <prepayment-form
        :model="model"
        :cashbox-list="cashboxList"
        @cashbox-changed="checkCashboxType">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                v-if="$can('payments.create-clinic')"
                type="primary"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                v-if="$can('payments.create-clinic')"
                type="primary"
                :disabled="disablePrint"
                @click="createAndPrint">
                {{ __('Добавить и печатать') }}
            </el-button>
        </div>
    </prepayment-form>
</template>
<script>
import Prepayment from '@/models/patient/prepayment';
import CreateMixin from '@/mixins/generic-create';
import PrepaymentForm from './Form.vue';
import printer from '@/services/print';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        PrepaymentForm,
    },
    props: {
        patient: Object,
        cashier: Object,
        cashboxList: {
            type: Array,
            default: () => [],
        },
        nonFiscalCashboxId: {
            type: [Number, String],
            default: null,
        },
        activeShift: {
            type: Object,
            default: () => {},
        }
    },
    data() {
        return {
            model: new Prepayment({
                patient_id: this.patient.id,
                cashier_id: this.cashier.id,
                cashbox_id: null,
                specialization_id: null,
                money_reciever_cashbox_id: this.activeShift ? this.activeShift.money_reciever_cashbox_id : null,
                checkbox_money_reciever_id: this.activeShift ? this.activeShift.money_reciever_id : null,
                money_reciever_id: this.activeShift ? this.activeShift.money_reciever_id : null,
            }),
            disablePrint: true,
        }
    },
    watch: {
        ['model.cashbox_id'](val) {
            this.disablePrint = val != this.nonFiscalCashboxId;
        },
    },
    methods: {
        createAndPrint() {
           this.model.save().then((response) => {
                 this.$emit('printCheck', response.response.data);
            }).catch((e) => {
                this.onSaveError(e);
                this.$displayErrors(e);
            });
        },
        checkCashboxType(val) {
            this.disablePrint = val != this.nonFiscalCashboxId;
        },
    }
}
</script>
