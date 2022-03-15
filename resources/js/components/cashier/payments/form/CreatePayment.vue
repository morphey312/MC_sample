<template>
    <div class="sections-wrapper" v-loading="saving">
        <Alerts
            :patient="patient"
            :do-not-take-payment="showDoNotTakePayment"
        ></Alerts>
        <drawer :open="displayFilter">
            <section class="grey pb-0 pt-10">
                <payment-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters"/>
            </section>
        </drawer>
        <section
            class="grey-cap flex-content pb-0 price-grid"
            :style="{'height': listHeight}">
            <payment-list
                ref="table"
                :filters="filters"
                :model="model"
                :cashier="cashier"
                :deposit-amount="depositAmount"
                :cashbox-list="cashboxList"
                :invalid="invalid"
                :non-fiscal-cashbox-id="nonFiscalCashboxId"
                :non-fiscal-non-cash-cashbox-id="nonFiscalNonCashCashboxId"
                :active-shift="activeShift"
                :checkbox-cashboxes="checkboxCashboxes"
                @loaded="listLoaded"
                @header-filter-updated="syncFilters"
                @calc-total="calcTotal"/>
        </section>
        <section class="pt-0">
            <div class="form-footer text-right">
                <b>{{ __('Итого к оплате') }} {{ $formatter.numberFormat(totalToPay) }} {{ __('грн') }}</b>
                &nbsp;/&nbsp;
                <span class="mr-10">
                    {{ __('Аванс:') }} {{ availableDeposit }} {{ __('грн') }}
                </span>
                <el-button
                    @click="cancel">
                    {{ buttonReturn }}
                </el-button>
                <el-button
                    v-if="$can('payments.create-clinic')"
                    :disabled="disableActions"
                    @click="create">
                    {{ __('Добавить') }}
                </el-button>
                <el-button
                    v-if="$can('payments.create-clinic')"
                    type="primary"
                    :disabled="disableActions"
                    @click="createAndPrint">
                    {{ __('Добавить и печатать') }}
                </el-button>
            </div>
        </section>
    </div>
</template>
<script>
import Patient from '@/models/patient';
import Payment from '@/models/payment';
import PaymentFilter from './create/Filter.vue';
import Alerts from './payment/Alerts.vue';
import PaymentList from './create/List.vue';
import DepositForm from './deposit/FormCreate.vue';
import PrepaymentForm from './prepayment/FormCreate.vue';
import ManageMixin from '@/mixins/manage';
import CheckStyleMixin from './mixins/style';
import CashboxMixin from '@/components/cashier/mixins/cashbox';
import BatchRequest from '@/services/batch-request';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import CONSTANTS from '@/constants';
import printer from '@/services/print';
import CheckPrint from './Print.vue';
import PrepaymentRepository from '@/repositories/patient/prepayment';

export default {
    mixins: [
        ManageMixin,
        CheckStyleMixin,
        CashboxMixin,
    ],
    components: {
        PaymentFilter,
        PaymentList,
        Alerts
    },
    props: {
        patient: Object,
        cashier: Object,
        cashboxes: {
            type: Array,
            default: () => [],
        },
        checkboxCashboxes: {
            type: Array,
            default: () => [],
        },
        activeShift: {
            type: Object,
            default: () => {},
        }
    },
    data() {
        return {
            model: new Patient({id: this.patient.id}),
            displayFilter: true,
            batchRequest: new BatchRequest('/api/v1/payments/batch'),
            saving: true,
            cashboxList: {
                all: [],
                nonFiscal: [],
            },
            paymentDestinations: [],
            services: [],
            analysisPaymentDestination: null,
            medicinePaymentDestination: null,
            depositAmount: 0,
            disableActions: true,
            buttonReturn: __('Отмена'),
            totalToPay: 0,
            invalid: [],
            nonFiscalCashboxId: this.getCashNonPrinterCashboxId(this.cashboxes),
            nonFiscalNonCashCashboxId: this.getNonCashNonFiscalCahboxId(this.cashboxes),
            prepayments: [],
            showDoNotTakePayment: false,
        }
    },
    computed: {
        listHeight() {
            return this.displayFilter ? '360px' : '560px';
        },
        availableDeposit() {
            let table = this.getTable();
            if (table) {
                return this.$formatter.numberFormat(this.depositAmount - table.totalUsedDeposit);
            }
            return this.depositAmount;
        },
    },
    watch: {
        ['filters.appointment_clinic'](val) {
            this.getPatientDepositAmount();
        }
    },
    mounted() {
        this.getPatientDepositAmount();
        this.getPaymentDestinations();
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
        getDefaultFilters() {
            return {
                appointment_clinic: this.cashier.clinic_id,
                patient: this.patient.id,
            }
        },
        getFilterUid() {
            return false;
        },
        toggleFilter(val) {
            this.displayFilter = val;
        },
        cancel() {
            this.$emit('cancel');
        },
        calcTotal() {
            this.totalToPay = this.getData().reduce((total, row) => {
                return total + (isNaN(row.payed_amount) ? 0 : row.payed_amount);
            }, 0);
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
        listLoaded() {
            let data = this.getData();
            this.services = data;
            this.setDoNotTakePayments(data);
            this.disableActions = data.length === 0;
        },
        getPaymentDestinations(specialization) {
            let repo = new PaymentDestinationRepository();
            repo.fetchList().then((response) => {
                this.paymentDestinations = response;
                let analysisDestination = response.find((row) => {
                    return row.additional_service_mark === CONSTANTS.PAYMENT_DESTINATION.ADDITIONAL_MARK.FOR_ANALYSES;
                });
                let medicineDestination = response.find((row) => {
                    return row.additional_service_mark === CONSTANTS.PAYMENT_DESTINATION.ADDITIONAL_MARK.FOR_MEDICINES;
                });
                this.analysisPaymentDestination = analysisDestination ? analysisDestination.id : null;
                this.medicinePaymentDestination = medicineDestination ? medicineDestination.id : null;
            });
        },
        getPatientDepositAmount() {
            this.model.fetch(['accounts']).then((response) => {
                this.depositAmount = Number(this.model.getClinicDepositBalance(this.filters.appointment_clinic));
                this.saving = false;
            });
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
            })
        },
        getFilledRows() {
            return this.getData().filter((row) => {
                return this.isValidAmount(row.payed_amount)
                    || this.isValidAmount(row.deposit)
                    || (!this.isValidAmount(row.payed_amount) && _.isFilled(row.prepayment_id) && _.isFilled(row.cashbox_id));
            });
        },
        getPaymentDestination(row) {
            if (row.container_type == null) {
                return row.payment_destination_id;
            }

            if (row.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES) {
                return this.analysisPaymentDestination;
            }

            if (row.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.MEDICINES) {
                return this.medicinePaymentDestination;
            }

            return null;
        },
        getIncomePaymentAttributes(row, attributes) {
            return {
                amount: row.amount,
                payed_amount: row.payed_amount,
                service_id: row.id,
                discount: row.discount,
                cashbox_id: row.cashbox_id,
                doctor_id: row.doctor_id,
                clinic_id: row.clinic_id,
                is_cash: row.is_cash,
                cashier_id: this.cashier.id,
                appointment_id: row.appointment_id,
                payment_destination_id: this.getPaymentDestination(row),
                patient_id: this.patient.id,
                type: CONSTANTS.PAYMENT.TYPES.INCOME,
                comment: row.comment,
                money_reciever_cashbox_id: this.getMoneyRecieverCashbox(row),
                checkbox_money_reciever_id: row.money_reciever ? row.money_reciever.id : null,
                ...attributes,
                ...((row.cashbox_id == this.nonFiscalNonCashCashboxId) ? {
                    created_at: row.created_at
                } : {}),
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
        getReturnDepositAttributes(row, attributes) {
            return {
                amount: row.deposit,
                payed_amount: row.deposit,
                cashbox_id: row.cashbox_id,
                cashier_id: this.cashier.id,
                patient_id: this.patient.id,
                clinic_id: row.clinic_id,
                type: CONSTANTS.PAYMENT.TYPES.EXPENSE,
                is_deposit: true,
                ...attributes,
            };
        },
        getPaymentRows() {
            let payments = [];
            this.getFilledRows().forEach((row) => {
                let timestamp = Date.now();
                if (row.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES) {
                    if (this.validAnalysisAmount(row)) {
                        if (this.isValidAmount(row.deposit)) {
                            if (row.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES) {
                                row.items.forEach((item) => {
                                    if (this.isValidAmount(item.deposit)) {
                                        payments.push(
                                            new Payment(this.getReturnDepositAttributes(item, {timestamp}))
                                        );
                                        payments.push(
                                            new Payment(this.getIncomePaymentAttributes(item, {
                                                    amount: item.deposit,
                                                    payed_amount: item.deposit,
                                                    from_deposit: true,
                                                    timestamp,
                                                })
                                            )
                                        );
                                    }
                                });
                            }
                        }
                        row.items.forEach((item) => {
                            if (this.isValidAmount(item.payed_amount)) {
                                payments.push(
                                    new Payment(this.getIncomePaymentAttributes(item, {
                                        amount: item.payed_amount,
                                        comment: row.comment ? row.comment : item.comment
                                    }))
                                );
                            }
                        });
                    } else {
                        this.warnAnalysisAmount();
                        return [];
                    }
                } else {
                    if (this.isValidAmount(row.deposit)) {
                        payments.push(
                            new Payment(this.getReturnDepositAttributes(row, {timestamp}))
                        );
                        payments.push(
                            new Payment(this.getIncomePaymentAttributes(row, {
                                    amount: row.deposit,
                                    payed_amount: row.deposit,
                                    from_deposit: true,
                                    timestamp,
                                })
                            )
                        );
                    }
                    if (this.isValidAmount(row.payed_amount)) {
                        payments.push(
                            new Payment(this.getIncomePaymentAttributes(row))
                        );
                    }
                }
                if (row.prepayment_id && row.prepayment_id.length) {
                    row.prepayment_id.forEach((id) => {
                        this.prepayments.push({
                            id: id,
                            used: true,
                        });
                    })
                    let prepaymentExpense = new Payment(this.getReturnDepositAttributes(row, {
                        amount: row.prepayed,
                        payed_amount: row.prepayed,
                        is_prepayment: 1,
                        from_deposit: true,
                        cashbox_id: this.nonFiscalCashboxId,
                        timestamp,
                    }));
                    let prepaymentIncome = new Payment(this.getIncomePaymentAttributes(row, {
                        amount: row.prepayed,
                        payed_amount: row.prepayed,
                        cashbox_id: this.nonFiscalCashboxId,
                        is_prepayment: 1,
                        timestamp
                    }));
                    payments.push(prepaymentExpense);
                    payments.push(prepaymentIncome);
                }
            });
            return payments;
        },
        warnAnalysisAmount() {
            return this.$confirm(__('Разнесите деньги за анализы в записи'), () => {
            });
        },
        isValidAmount(amount) {
            return _.isFilled(amount) && amount > 0;
        },
        validAnalysisAmount(row) {
            let innerAmount = _.sumBy(row.items, 'payed_amount');
            let containerAmount = 0;
            let deposit = row.deposit;
            row.items.forEach(item => {
                item.doctor_id = row.doctor_id;
                item.created_at = row.created_at;
            });
            if (this.isValidAmount(innerAmount)) {
                return true;
            }
            if (this.isValidAmount(row.payed_amount)) {
                containerAmount += row.payed_amount;
            }
            if (this.isValidAmount(row.deposit)) {
                containerAmount += row.deposit;
            }
            if (containerAmount === row.debt) {
                row.items.forEach(item => {
                    let analysisDebt = item.debt;
                    if (this.isValidAmount(deposit)) {
                        if (deposit >= analysisDebt) {
                            item.deposit = analysisDebt;
                            item.payed_amount = 0;
                            deposit -= Number(item.deposit);
                        } else {
                            item.deposit = deposit;
                            item.payed_amount = Number(analysisDebt) - Number(item.deposit);
                            deposit = 0;
                        }
                    } else {
                        item.payed_amount = item.debt;
                    }
                    item.cashbox_id = row.cashbox_id;
                });
                return true;
            }

            if (!_.isFilled(row.payed_amount)) {
                return true;
            }
            return false;

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
        checkServiceCodes(codes,clinicId) {
            let clinic = codes.find((item) => item.clinic_id === clinicId)
            return clinic.code ? true : false
        },
        validatePaymentForCheckbox(payment) {
            if (payment.cashbox_id && !payment.is_prepayment) {
                let cashbox = this.cashboxes.find((item) => item.id === payment.cashbox_id);
                if (cashbox.payment_method.for_checkbox) {
                    let service = this.services.find((item) => item.id === payment.service_id)
                    let checkbox = this.checkboxCashboxes.find((item) => item.payment_method_id === cashbox.payment_method.id)
                    if (!checkbox || checkbox.clinic_id !== payment.clinic_id) {
                        this.$error(__('Смена для выбранной формы оплаты не открыта!'))
                        return false
                    }
                    if (service.codes && !this.checkServiceCodes(service.codes,payment.clinic_id)) {
                        this.$error(__('Код для услуги {service} не указан!', {service: service.name}));
                        return false
                    }
                }
            }
            return true
        },
        create(onCreated = false) {
            this.disableActions = true;
            let payments = this.getPaymentRows();
            if (_.isEmpty(payments)) {
                this.disableActions = false;
                return;
            }
            this.batchRequest.reset();
            let isValid = true
            _.each(payments,(row) => {
                if (this.validatePaymentForCheckbox(row)) {
                    this.saving = true;
                    this.disableActions = true;
                    this.batchRequest.create(row);
                } else {
                    isValid = false
                }
            });
            if (!isValid) {
                this.disableActions = false
                return;
            }
            this.saving = true;
            this.invalid = [];
            this.batchRequest.submit().then((result) => {
                this.updatePrepayments().then(() => {
                    this.refresh();
                    this.calcTotal();
                    if (result.failure.length !== 0) {
                        this.$error(__('Не удалось сохранить некоторые данные'));
                    } else {
                        this.buttonReturn = __('Выход');
                        this.$info(__('Данные успешно обновлены'));
                    }
                    if (result.success.length != 0 && _.isFunction(onCreated)) {
                        onCreated(result.success);
                    }
                    this.getPatientDepositAmount();
                });
            }).catch((error) => {
                this.saving = false;
                this.disableActions = false;
                if (error.invalid) {
                    this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
                    this.invalid = error.invalid;
                }
                this.prepayments = [];
            });
        },
        prepareCheck(payment) {
            let checks = {};
            let checkId = payment.check_id;
            checks[checkId] = {
                checkId,
                cardNumber: payment.appointment ? payment.appointment.card_number : '',
                services: [{payed_amount: payment.payed_amount, name: payment.service.name_ua}],
                created: this.$moment(payment.check.created),
                clinic: payment.clinic,
                money_reciever: payment.money_reciever_name ? {name: payment.money_reciever_name} : payment.money_reciever,
            };
            return checks;
        },
        createAndPrint() {
            this.create((list) => {
                let checks = {};
                list.forEach((payment) => {
                    let checkId = payment.check_id;
                    if (_.isFilled(checkId)) {
                        if (checks[checkId]) {
                            checks[checkId].services.push(this.getPaymentInfo(payment));
                        } else {
                            checks[checkId] = {
                                checkId,
                                cardNumber: payment.appointment ? payment.appointment.card_number : '',
                                services: [this.getPaymentInfo(payment)],
                                created: this.$moment(payment.check.created),
                                clinic: payment.clinic,
                                money_reciever: payment.money_reciever_name ? {name: payment.money_reciever_name} : payment.money_reciever,
                            };
                        }
                    }
                });
                this.printChecks(checks);
            });
        },
        printChecks(checks) {
            if (_.isEmpty(checks)) {
                return;
            }

            printer.newPrinter().printComponent(CheckPrint, {checks}, null, this.getCheckSettings());
            printer.deleteAllIframes();
        },
        getPaymentInfo(payment) {
            if (payment.service_container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES) {
                return {
                    payed_amount: payment.payed_amount,
                    name: payment.service.analysis_items.join(', '),
                };
            }
            return {
                payed_amount: payment.payed_amount,
                name: payment.service.name_ua,
            }
        },
        addDeposit() {
            this.$modalComponent(DepositForm, {
                patient: this.model,
                cashier: this.cashier,
                cashboxList: this.checkboxCashboxes && this.checkboxCashboxes.length ? this.cashboxList.all : this.cashboxList.nonFiscal,
                nonFiscalCashboxId: this.nonFiscalCashboxId,
                activeShift: this.activeShift,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                created: (dialog, payment) => {
                    dialog.close();
                    this.getPatientDepositAmount();
                },
                printCheck: (dialog, payment) => {
                    let check = this.prepareCheck(payment);
                    this.printChecks(check);
                    dialog.close();
                },

            }, {
                header: __('Добавить аванс'),
                width: '355px',
            });
        },
        addPrepayment() {
            this.$modalComponent(PrepaymentForm, {
                patient: this.model,
                cashier: this.cashier,
                cashboxList: this.checkboxCashboxes && this.checkboxCashboxes.length ? this.cashboxList.all : this.cashboxList.nonFiscal,
                nonFiscalCashboxId: this.nonFiscalCashboxId,
                activeShift: this.activeShift,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                created: (dialog, payment) => {
                    dialog.close();
                },
                printCheck: (dialog, payment) => {
                    payment.payment.service = {name_ua: payment.service_name};
                    let check = this.prepareCheck(payment.payment);
                    this.printChecks(check);
                    dialog.close();
                },
            }, {
                header: __('Добавить предоплату'),
                width: '355px',
            });
        },
        setDoNotTakePayments(rows) {
            let doNotTakePayment = rows.filter((row) => (row.appointment) ? row.appointment.do_not_take_payment === true : false);

            if (doNotTakePayment.length) {
                this.showDoNotTakePayment = true;
            }
        },
    },
}
</script>
