<template>
    <div class="sections-wrapper" v-loading="saving">
        <drawer :open="displayFilter">
            <section class="grey pb-0 pt-10">
                <payment-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters"  />
            </section>
        </drawer>
        <section
            class="grey-cap flex-content pb-0"
            :style="{'height': listHeight}">
                <payment-list
                    ref="table"
                    :filters="filters"
                    :model="model"
                    :cashier="cashier"
                    :cashbox-list="cashboxList"
                    :cashboxes="cashboxes"
                    :checkbox-cashboxes="checkboxCashboxes"
                    :is-online-cashier="isOnlineCashier"
                    :active-shift="activeShift"/>
        </section>
        <section class="pt-0">
            <div class="form-footer text-right">
                <el-button
                    @click="cancel">
                    {{ buttonReturn }}
                </el-button>
                <el-button
                    @click="create">
                    {{ __('Вернуть деньги') }}
                </el-button>
                <el-button
                    v-if="$can('cash-transfers.non-fiscal-payments')"
                    type="primary"
                    @click="createAndPrint">
                    {{ __('Вернуть деньги и печатать') }}
                </el-button>
            </div>
        </section>
    </div>
</template>
<script>
import Payment from '@/models/payment';
import Patient from '@/models/patient';
import PaymentFilter from './refund/Filter.vue';
import PaymentList from './refund/List.vue';
import ManageMixin from '@/mixins/manage';
import BatchRequest from '@/services/batch-request';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import CONSTANTS from '@/constants';
import printer from '@/services/print';
import PrepaymentRepository from '@/repositories/patient/prepayment';

export default {
    components: {
        PaymentFilter,
        PaymentList,
    },
    mixins: [
        ManageMixin,
    ],
    props: {
        patient: Object,
        cashier: Object,
        cashboxes: {
            type: Array,
            default: () => [],
        },
        isOnlineCashier: {
            type: Boolean,
            default: false,
        },
        checkboxCashboxes: {
            type: Array,
            default: () => [],
        },
        activeShift: {
            type: Object,
            default: () => {},
        },
    },
    data() {
        return {
            model: new Patient({id: this.patient.id}),
            displayFilter: true,
            batchRequest: new BatchRequest('/api/v1/payments/batch'),
            saving: false,
            cashboxList: {
                all: [],
                nonFiscal: [],
            },
            paymentDestinations: [],
            analysisPaymentDestination: null,
            buttonReturn: __('Отмена'),
            prepayments: [],
        }
    },
    computed: {
        listHeight() {
            return this.displayFilter ? '360px' : '560px';
        },
    },
    mounted() {
        this.getCashboxList();
        this.$eventHub.$on('broadcast.checkbox_check_created', ({data}) => {
            this.saving = false;
            this.disableActions = false;
            printer.newPrinter().printRawHtml("<div style='margin-top: 0; width: 277px'>" + data.body + "</div>");
        });
    },
    beforeDestroy() {
        this.$eventHub.$off('broadcast.checkbox_check_created');
    },
    methods: {
        getFilterUid() {
            return false;
        },
        getDefaultFilters() {
            return {
                appointment_clinic: this.cashier.clinic_id,
                patient: this.patient.id,
            }
        },
        toggleFilter(val) {
            this.displayFilter = val;
        },
        cancel() {
            this.$emit('cancel');
        },
        getTable() {
            return this.$refs.table;
        },
        getData() {
            return this.getTable().getData();
        },
        refresh() {
            return this.getTable().refresh();
        },
        getCashboxList() {
            if (this.cashboxes.length === 0) {
                return;
            }

            this.cashboxList = {
                all: [],
                nonFiscal: [],
            };

            this.cashboxes.forEach((box) => {
                let isFiscal = box.payment_method.clinics.find((clinic) => clinic.clinic_id === this.cashier.clinic_id).is_fiscal;
                if (this.checkboxCashboxes && this.checkboxCashboxes.length) {
                    if (box.payment_method.is_enabled && !box.payment_method.pc_payment) {
                        this.cashboxList.all.push({
                            id: box.id,
                            forCheckbox: box.payment_method.for_checkbox,
                            useCash:  box.payment_method.use_cash,
                            value: box.payment_method.name,
                        });
                    }
                }

                if (!isFiscal) {
                    if (box.payment_method.is_enabled && !box.payment_method.pc_payment) {
                        this.cashboxList.nonFiscal.push({
                            id: box.id,
                            forCheckbox: box.payment_method.for_checkbox,
                            useCash:  box.payment_method.use_cash,
                            value: box.payment_method.name,
                        })
                    }
                }
            });
        },
        getFilledRows() {
            return this.getData().filter((row) => {
                return this.isValidAmount(row.amount);
            });
        },
        getExpensePaymentAttributes(row, attributes) {
            return {
                service_id: row.id,
                cashbox_id: row.cashbox_id,
                doctor_id: row.doctor_id,
                clinic_id: row.clinic_id,
                is_cash: row.is_cash,
                cashier_id: this.cashier.id,
                appointment_id: row.appointment_id,
                patient_id: this.model.id,
                type: CONSTANTS.PAYMENT.TYPES.EXPENSE,
                is_deposit: row.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES ? _.isVoid(row.appointment_id) : false,
                is_prepayment: row.is_prepayment,
                comment: row.comment,
                is_technical: row.is_technical,
                checkbox_money_reciever_id: row.money_reciever ? row.money_reciever.id : null,
                money_reciever_cashbox_id: this.getMoneyRecieverCashbox(row),
                ...attributes,
            };
        },
        getMoneyRecieverCashbox(row) {
            const paymentMoneyRecieverId = row.money_reciever ? row.money_reciever.id : null;

            if (paymentMoneyRecieverId) {
                const cashboxMoneyReciever = this.checkboxCashboxes.find((cashbox) => cashbox.money_reciever_id === paymentMoneyRecieverId);
                return cashboxMoneyReciever ? cashboxMoneyReciever.money_reciever_cashbox_id : null;
            }

            return null;
        },
        getPaymentRows() {
            let payments = [];
            this.getFilledRows().forEach((row) => {
                payments.push(
                    new Payment(this.getExpensePaymentAttributes(row, {
                            amount: row.amount,
                            payed_amount: row.amount,
                            payment_destination_id: row.payment_destination_id,
                            is_deposit: (row.deposit_types && row.deposit_types.is_deposit) ? row.deposit_types.is_deposit : false,
                        })
                    )
                );
                if (row.prepayment_id) {
                    let newAmount = Number(row.paid - row.amount);
                    this.prepayments.push({
                        id: row.prepayment_id,
                        amount: newAmount,
                        ...(newAmount == 0
                            ? {used: false}
                            : {}
                        ),
                    });
                }
            });
            return payments;
        },
        isValidAmount(amount) {
            return _.isFilled(amount) && amount > 0;
        },
        updatePrepayments() {
            if (this.prepayments.length != 0) {
                let prepayment = new PrepaymentRepository();
                return prepayment.saveAttributes(this.prepayments).then(() => {
                    this.prepayments = [];
                    return Promise.resolve();
                });
            }
            return Promise.resolve();
        },
        create() {
            let payments = this.getPaymentRows();

            this.batchRequest.reset();
            payments.forEach((row) => {
                this.batchRequest.create(row);
            });
            this.saving = true;
            this.invalid = [];
            this.batchRequest.submit().then((result) => {
                this.updatePrepayments()
                this.refresh();
                this.saving = false;
                if (result.failure.length !== 0) {
                    this.$error(__('Не удалось сохранить некоторые данные'));
                    this.invalid = result.failure;
                } else {
                    this.buttonReturn = __('Выход');
                    this.$info(__('Данные успешно обновлены'));
                }
            }).catch((error) => {
                this.saving = false;
                if (error.invalid) {
                    this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
                    this.invalid = error.invalid;
                }
                this.prepayments = [];
            });
        },
        createAndPrint() {

        },
    },
}
</script>
