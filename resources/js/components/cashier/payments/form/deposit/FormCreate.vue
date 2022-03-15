<template>
    <model-form :model="model">
        <form-select
            :entity="model"
            :options="clinics"
            property="clinic_id"
            :filterable="true"
            :label="__('Клиника')" />
        <form-select
            :entity="model"
            :options="cashboxList"
            property="cashbox_id"
            :label="__('Форма оплаты')"
        />
        <form-input
            :entity="model"
            property="amount"
            :label="__('Сумма, грн')"
        />
        <form-text
            :entity="model"
            property="comment"
            :rows="1"
            :autosize="true"
            :label="__('Примечание')"
        />
        <div class="form-footer text-right">
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
    </model-form>
</template>
<script>
import Payment from '@/models/payment';
import ClinicRepository from '@/repositories/clinic';
import CONSTANTS from '@/constants';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
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
        },
    },
    data() {
        return {
            model: new Payment({
                patient_id: this.patient.id,
                cashier_id: this.cashier.id,
                type: CONSTANTS.PAYMENT.TYPES.INCOME,
                is_deposit: true,
                money_reciever_cashbox_id: this.activeShift ? this.activeShift.money_reciever_cashbox_id : null,
                checkbox_money_reciever_id: this.activeShift ? this.activeShift.money_reciever_id : null,
                money_reciever_id: this.activeShift ? this.activeShift.money_reciever_id : null,
            }),
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('payments'),
            }),
            disablePrint: true,
        }
    },
    watch: {
        ['model.amount'](val) {
            this.model.payed_amount = val;
        },
        ['model.cashbox_id'](val) {
            if (val) {
                this.model.is_cash = this.cashboxList.find((cashbox) => cashbox.id === val).useCash;
            }

            this.disablePrint = val != this.nonFiscalCashboxId;
        },
    },
    methods: {
        create() {
            this.model.save().then((response) => {
                this.$emit('created', this.model);
            }).catch((e) => {
                this.onSaveError(e);
                this.$displayErrors(e);
            });
        },
        createAndPrint() {
            this.model.save().then((response) => {
                this.$emit('printCheck', response.response.data);
            }).catch((e) => {
                this.onSaveError(e);
                this.$displayErrors(e);
            });
        },
    }
}
</script>
