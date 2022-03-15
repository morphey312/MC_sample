<template>
    <div v-loading="model.loading || model.saving">
        <form-input
            :entity="model"
            property="amount"
            :label="__('Сумма, грн')"
            :placeholder="__('Введите сумму')"
        />
        <form-select
            v-if="activeShift"
            :entity="selectedShift"
            property="id"
            :options="cashierShifts"
            :clearable="true"
            :label="__('Активная касса')"
        />
        <form-select
            :entity="model"
            property="destination_id"
            :options="filteredCashCashboxes"
            :clearable="true"
            :label="__('Форма оплаты')"
            :disabled="forCheckbox"
        />
        <el-checkbox
            v-if="activeShift"
            v-model="forCheckbox"
        >
            {{ __('Провести через Checkbox') }}
        </el-checkbox>
        <div class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                :disabled="disabledCreate"
                type="primary"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import CashTransfer from '@/models/employee/cashbox/cash-transfer';
import CreateMixin from '@/mixins/generic-create';
import checkbox from '@/services/checkbox';
import printer from '@/services/print';
import CashboxMixin from '@/components/cashier/mixins/cashbox';
import Check from "@/models/checkbox/check";
import CONSTANTS from '@/constants';

export default {
    mixins: [
        CreateMixin,
        CashboxMixin,
    ],
    props: {
        cashier: Object,
        activeShift: Object,
        checkboxCashboxes: Array,
        cashboxes: Array,
    },
    data() {
        return {
            model: new CashTransfer({
                cashier_id: this.cashier.id,
                destination_id: null,
            }),
            loading: false,
            forCheckbox: false,
            cashierShifts: [],
            selectedShift: {
                id: null,
                accessToken: null,
            },
            filteredCashCashboxes: [],
        }
    },
    computed: {
        disabledCreate() {
            if (this.activeShift && this.forCheckbox) {
                return !(this.selectedShift.id && this.model.amount && this.model.amount != 0);
            } else {
                if (!this.model.destination_id || !this.model.amount || this.model.amount == 0) {
                    return true;
                }
            }

            return false;
        },
    },
    watch: {
        ['selectedShift.id'](val) {
            if (val) {
                this.selectedShift.accessToken = this.cashierShifts.find(item => item.id === val).access_token;
                this.selectedShift.money_reciever_cashbox_id = this.cashierShifts.find(item => item.id === this.selectedShift.id).money_reciever_cashbox_id;
                this.getFilteredCashboxes(this.cashboxes, true);
            } else {
                this.selectedShift.accessToken = null;
                this.getFilteredCashboxes(this.cashboxes, false);
            }

            this.model.destination_id = null;
        },
    },
    mounted() {
        this.getShifts();
        this.getFilteredCashboxes(this.cashboxes, false);
    },
    methods: {
        getFilteredCashboxes(cashboxes, isFiscal = true) {
            this.filteredCashCashboxes = this.filterCashCashboxes(cashboxes, isFiscal);
            this.filteredCashCashboxes.forEach((box) => {
                box.value = box.payment_method.name;
            })
        },
        getShifts() {
            this.checkboxCashboxes.forEach((item) => {
                this.cashierShifts.push({
                    id: item.money_reciever_id,
                    money_reciever_cashbox_id: item.money_reciever_cashbox_id,
                    value: item.money_reciever_cashbox_name,
                    access_token: item.access_token,
                });
            });
        },
        create() {
            this.$clearErrors();
            if (this.forCheckbox) {
                checkbox.createCurrencyCheck(this.selectedShift.accessToken,this.model.amount).then((result) => {
                    new Check({
                        money_reciever_cashbox_id: this.selectedShift.money_reciever_cashbox_id,
                        employee_id : this.$store.state.user.employee.id,
                        body: result.body,
                        amount: this.model.amount,
                        type: CONSTANTS.CHECKBOX_CHECKS.TYPE.TOKEN,
                    }).save().then(() => {
                        printer.newPrinter().printRawHtml("<div style='margin-top: 0; width: 277px'>" + result.body + "</div>");
                    });
                })
            } else {
                this.model.save().then((response) => {
                    this.$emit('created', this.model);
                }).catch((e) => {
                    this.onSaveError(e);
                    this.$displayErrors(e);
                });
            }
        },
    }
}
</script>
