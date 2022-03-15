<template>
    <div v-loading="model.loading || model.saving">
        <form-select
            :entity="selectedCashier"
            :options="cashiers"
            property="id"
            :label="__('Выберите сотрудника')"
            :disabled="forCheckbox"
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
        <form-input
            :entity="model"
            property="amount"
            :label="__('Сумма, грн')"
            :placeholder="__('Введите сумму')"
        />
        <div
            v-if="activeShift"
            class="input-label form-input"
        >
            {{ __('В кассе Checkbox:') + ' ' }}{{ cashierBalance ? cashierBalance + ' грн' : '' }}
        </div>
        <div>
            <el-checkbox
                v-if="activeShift"
                v-model="forCheckbox"
            >
                {{ __('Провести через Checkbox') }}
            </el-checkbox>
        </div>
        <div class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                :disabled="disabledAction"
                type="primary"
                @click="transfer">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import CreateMixin from '@/mixins/generic-create';
import CashboxMixin from '@/components/cashier/mixins/cashbox';
import TransferMixin from '@/components/cashier/mixins/transfer';
import checkbox from '@/services/checkbox';
import printer from '@/services/print';
import CONSTANTS from '@/constants';
import Check from "@/models/checkbox/check";

export default {
    mixins: [
        CreateMixin,
        CashboxMixin,
        TransferMixin,
    ],
    props: {
        activeShift: Object,
        checkboxCashboxes: Array,
    },
    data() {
        return {
            forCheckbox: false,
            cashierShifts: [],
            selectedShift: {
                id: null,
                accessToken: null,
            },
            selectedCashier: {
                id: null,
                cashboxes: [],
            },
            cashierBalance: null,
            filteredCashCashboxes: [],
        }
    },
    computed: {
        disabledAction() {
            if (this.activeShift && this.forCheckbox) {
                return !(this.selectedShift && this.cashierBalance)
                    || !(this.forCheckbox && this.cashierBalance - this.model.amount >= 0)
                    || !(this.model.amount && this.model.amount != 0);
            } else {
                if (!this.model.destination_id) {
                    return true;
                }
            }

            return false;
        },
        disabledCreate() {
            if ((this.activeShift && this.forCheckbox && !this.selectedShift.id) || (this.activeShift && !this.forCheckbox && !this.model.destination_id)) {
                return true;
            } else {
                if (!this.activeShift && !this.model.destination_id) {
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
                this.getCashierBalance();

                if (this.selectedCashier.id) {
                    this.getFilteredCashboxes(this.selectedCashier.cashboxes, true);
                }
            } else {
                this.selectedShift.accessToken = null;
                this.cashierBalance = null;

                if (this.selectedCashier.id) {
                    this.getFilteredCashboxes(this.selectedCashier.cashboxes, false);
                }
            }

            this.model.destination_id = null;
        },
        ['selectedCashier.id'](val) {
            if (val) {
                this.selectedCashier.cashboxes = this.cashiers.find(item => item.id === val).cashboxes;
                this.getFilteredCashboxes(this.selectedCashier.cashboxes, false);
            }
        },
        ['model.destination_id'](val) {
            if (val) {
                const cashierCashboxes = this.cashiers.find(item => item.id === this.$store.state.user.employee_id).cashboxes;
                const destinationCashbox = this.filteredCashCashboxes.find((box) => box.id === val);

                this.model.source_id = cashierCashboxes.find((box) => box.payment_method_id === destinationCashbox.payment_method_id).id;
            }
        },
    },
    mounted() {
        this.getShifts();
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
                    value: item.money_reciever_cashbox_name,
                    money_reciever_cashbox_id: item.money_reciever_cashbox_id,
                    access_token: item.access_token,
                });
            });
        },
        getCashierBalance() {
            checkbox.getCashierBalance(this.selectedShift.accessToken).then((result) => {
                this.cashierBalance = result.body;
            });
        },
        castCashiers(data) {
            let list = [];
            data.forEach((item) => {
                if (item.cashboxes) {
                    if (item.cashboxes) {
                        list.push({
                            id: item.id,
                            value: item.value,
                            cashboxes: item.cashboxes,
                        })
                    }
                }
            });

            return list;
        },
        create() {
            this.$clearErrors();

            if (this.forCheckbox) {
                checkbox.createCurrencyCheck(this.selectedShift.accessToken,-this.model.amount).then((result) => {
                    new Check({
                        money_reciever_cashbox_id: this.selectedShift.money_reciever_cashbox_id,
                        body: result.body,
                        employee_id : this.$store.state.user.employee.id,
                        amount: this.model.amount,
                        type: CONSTANTS.CHECKBOX_CHECKS.TYPE.EXTRACT,
                    }).save().then(() => {
                        printer.newPrinter().printRawHtml("<div style='margin-top: 0; width: 277px'>" + result.body + "</div>")
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
        transfer() {
            if (!this.forCheckbox) {
                if (_.isVoid(this.model.destination_id)) {
                    return this.$error(__('Выберите форму оплаты'));
                }

                if (this.isInvalidAmount()) {
                    return this.$error(__('Укажите корректное значение суммы выемки'));
                }
            }

            return this.create();
        },
    },
}
</script>
