<template>
    <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
        <el-tab-pane
            :lazy="true"
            :label="__('Платежи: {period} грн', {period: periodDifference})"
            name="payments" >
            <section class="darkgrey-cap shrinkable pt-0">
                <payment-list
                    ref="table"
                    :filters="filter"
                    :payment-methods="paymentMethods"
                    @selection-changed="setActiveItem"
                    @loaded="loaded"
                    @header-filter-updated="syncFilters"
                    @show-patient-payments="showPatientPayments" >
                    <template slot="buttons">
                        <div class="buttons" >
                            <form-button
                                v-if="$canCreate('payments')"
                                :text="__('Добавить платеж')"
                                icon="plus"
                                @click="createPayment" />
                            <form-button
                                v-if="$canUpdate('payments')"
                                :disabled="activeItem === null || !$canManage('payments.update', [activeItem.clinic_id])"
                                :text="__('Редактировать')"
                                icon="edit"
                                @click="editPayment" />
                            <form-button
                                v-if="$canCreate('payments')"
                                :text="__('Возврат')"
                                icon="arrow-circle"
                                @click="refundPayment" />
                            <el-dropdown class="ml-10">
                                <el-button >
                                    {{ __('Еще') }}
                                </el-button>
                                <el-dropdown-menu slot="dropdown">
                                    <el-dropdown-item v-if="$canDelete('payments')">
                                        <el-button
                                            type="text"
                                            :disabled="deletionAllowed()"
                                            @click="deletePayment">
                                            {{ __('Удалить') }}
                                        </el-button>
                                    </el-dropdown-item>
                                    <el-dropdown-item v-if="$can('payments.show-log')">
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null "
                                            @click="showLog">
                                            {{ __('Операции') }}
                                        </el-button>
                                    </el-dropdown-item>
                                    <el-dropdown-item>
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null || !activeItem.check"
                                            @click="print">
                                            {{ __('Печать копии чека') }}
                                        </el-button>
                                    </el-dropdown-item>
                                    <el-dropdown-item v-if="$canAccess('patient-cabinet')">
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null"
                                            @click="goToPatientCabinet">
                                            {{ __('Перейти в личный кабинет') }}
                                        </el-button>
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown>
                        </div>
                        <div class="table-summary">
                            {{ __('Разм. монета') }} {{ initialAmount }} {{ __('грн/Итого:') }} {{ periodDifference }} {{ __('грн') }}
                            {{ __('Итого Мед. услуги:') }} {{ totalServices }} {{ __('грн') }}
                            {{ __('Итого Мед. Анализы:') }} {{ totalAnalisys }} {{ __('грн') }}
                        </div>
                    </template>
                </payment-list>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="false"
            :label="__('Мед.услуги: {period} грн', {period: totalServices})"
            name="services" >
            <section class="darkgrey-cap shrinkable pt-0">
                <payment-services-list
                    ref="services"
                    :filters="filter"
                    :payment-methods="paymentMethods"
                    @selection-changed="setActiveItem"
                    @header-filter-updated="syncFilters"
                >
                <template slot="buttons">
                        <div class="buttons" >
                            <form-button
                                v-if="$canCreate('payments')"
                                :text="__('Добавить платеж')"
                                icon="plus"
                                @click="createPayment" />
                            <form-button
                                v-if="$canUpdate('payments')"
                                :disabled="activeItem === null || !$canManage('payments.update', [activeItem.clinic_id])"
                                :text="__('Редактировать')"
                                icon="edit"
                                @click="editPayment" />
                            <form-button
                                v-if="$canCreate('payments')"
                                :text="__('Возврат')"
                                icon="arrow-circle"
                                @click="refundPayment" />
                            <el-dropdown class="ml-10">
                                <el-button >
                                    {{ __('Еще') }}
                                </el-button>
                                <el-dropdown-menu slot="dropdown">
                                    <el-dropdown-item v-if="$canDelete('payments')">
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null || !$canManage('payments.delete', [activeItem.clinic_id])"
                                            @click="deletePayment">
                                            {{ __('Удалить') }}
                                        </el-button>
                                    </el-dropdown-item>
                                    <el-dropdown-item v-if="$can('payments.show-log')">
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null "
                                            @click="showLog">
                                            {{ __('Операции') }}
                                        </el-button>
                                    </el-dropdown-item>
                                    <el-dropdown-item>
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null || !activeItem.check"
                                            @click="print">
                                            {{ __('Печать копии чека') }}
                                        </el-button>
                                    </el-dropdown-item>
                                    <el-dropdown-item v-if="$canAccess('patient-cabinet')">
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null"
                                            @click="goToPatientCabinet">
                                            {{ __('Перейти в личный кабинет') }}
                                        </el-button>
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown>
                        </div>
                        <div class="table-summary">
                            {{ __('Разм. монета') }} {{ initialAmount }} {{ __('грн/Итого:') }} {{ periodDifference }} {{ __('грн') }}
                            {{ __('Итого Мед. услуги:') }} {{ totalServices }} {{ __('грн') }}
                        </div>
                    </template>
                </payment-services-list>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="false"
            :label="__('Анализы: {period} грн', {period: totalAnalisys})"
            name="analysis" >
            <section class="darkgrey-cap shrinkable pt-0">
                <payment-analysis-list
                    ref="analysis"
                    :filters="filter"
                    :payment-methods="paymentMethods"
                    @selection-changed="setActiveItem"
                    @header-filter-updated="syncFilters"
                >
                <template slot="buttons">
                        <div class="buttons" >
                            <form-button
                                v-if="$canCreate('payments')"
                                :text="__('Добавить платеж')"
                                icon="plus"
                                @click="createPayment" />
                            <form-button
                                v-if="$canUpdate('payments')"
                                :disabled="activeItem === null || !$canManage('payments.update', [activeItem.clinic_id])"
                                :text="__('Редактировать')"
                                icon="edit"
                                @click="editPayment" />
                            <form-button
                                v-if="$canCreate('payments')"
                                :text="__('Возврат')"
                                icon="arrow-circle"
                                @click="refundPayment" />
                            <el-dropdown class="ml-10">
                                <el-button >
                                    {{ __('Еще') }}
                                </el-button>
                                <el-dropdown-menu slot="dropdown">
                                    <el-dropdown-item v-if="$canDelete('payments')">
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null || !$canManage('payments.delete', [activeItem.clinic_id])"
                                            @click="deletePayment">
                                            {{ __('Удалить') }}
                                        </el-button>
                                    </el-dropdown-item>
                                    <el-dropdown-item v-if="$can('payments.show-log')">
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null "
                                            @click="showLog">
                                            {{ __('Операции') }}
                                        </el-button>
                                    </el-dropdown-item>
                                    <el-dropdown-item>
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null || !activeItem.check"
                                            @click="print">
                                            {{ __('Печать копии чека') }}
                                        </el-button>
                                    </el-dropdown-item>
                                    <el-dropdown-item v-if="$canAccess('patient-cabinet')">
                                        <el-button
                                            type="text"
                                            :disabled="activeItem === null"
                                            @click="goToPatientCabinet">
                                            {{ __('Перейти в личный кабинет') }}
                                        </el-button>
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown>
                        </div>
                        <div class="table-summary">
                            {{ __('Разм. монета') }} {{ initialAmount }} {{ __('грн/Итого:') }} {{ periodDifference }} {{ __('грн') }}
                            {{ __('Итого Анализы:') }} {{ totalAnalisys}} {{ __('грн') }}
                        </div>
                    </template>
                </payment-analysis-list>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Остатки: {balance} грн', {balance: totalBalance})"
            name="balances" >
            <section class="darkgrey-cap shrinkable pt-0">
                <balance-list
                    ref="balance"
                    :balances="cashboxes" />
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Предоплаты')"
            name="prepayments" >
            <section class="darkgrey-cap shrinkable pt-0">
                <prepayment-list
                    ref="prepayments"
                    :filters="filter"
                    :payment-methods="paymentMethods"
                    @loaded="getPrepaymentTotal"
                    @selection-changed="setActiveItem"
                    @header-filter-updated="syncFilters"
                    @show-patient-payments="showPatientPayments" >
                    <template slot="buttons">
                        <div class="buttons">
                            <el-button
                                v-if="$canUpdate('payments')"
                                :disabled="activeItem === null || !$canManage('payments.update', [activeItem.clinic_id])"
                                @click="editPrepayment">
                                {{ __('Редактировать') }}
                            </el-button>
                            <el-button
                                v-if="$canAccess('patient-cabinet')"
                                :disabled="activeItem === null"
                                @click="goToPatientCabinet">
                                {{ __('Перейти в личный кабинет') }}
                            </el-button>
                            <el-button
                                :disabled="activeItem === null"
                                @click="showPrepaymentLog">
                                {{ __('Операции') }}
                            </el-button>
                            <el-button
                                :disabled="activeItem === null"
                                @click="printPrepayment">
                                {{ __('Печать копии чека') }}
                            </el-button>
                        </div>
                        <div class="table-summary">
                            {{ __('Оплачено:') }} {{ totalPrepaid }} / {{ __('Остаток:') }} {{ totalPrepaidRest }}
                        </div>
                    </template>
                </prepayment-list>
            </section>
        </el-tab-pane>
        <el-tab-pane
            v-if="$can('checkbox-checks.access')"
            :lazy="true"
            :label="__('Чеки')"
            name="checks">
            <section class="darkgrey-cap shrinkable pt-0">
                <ChecksList
                    ref="checks"
                    :filters="filter"
                    :initial-amount="initialAmount"
                    :checkbox-cashboxes="checkboxCashboxes"
                    @selection-changed="setActiveItem"
                    @header-filter-updated="syncFilters"
                >
                    <template slot="buttons">
                        <div class="buttons" >
                            <form-button
                                v-if="$canCreate('payments')"
                                :text="__('Добавить платеж')"
                                icon="plus"
                                @click="createPayment" />
                            <form-button
                                v-if="$canCreate('payments')"
                                :text="__('Возврат')"
                                icon="arrow-circle"
                                @click="refundPayment" />
                        </div>
                    </template>
                </ChecksList>
            </section>
        </el-tab-pane>
    </el-tabs>
</template>
<script>
import PaymentList from './List.vue';
import ChecksList from '../checkbox-checks/Checks'
import PaymentServicesList from './ServicesList.vue';
import PaymentAnalysisList from './AnalysisList.vue';
import BalanceList from './Balances.vue';
import PrepaymentList from './Prepayments.vue';
import SearchPatient from '@/components/patients/search/Search.vue';
import CreatePaymentHeaderAddon from './HeaderAddon.vue';
import ToggleModalFilter from '@/components/patients/search/ToggleFilter.vue';
import CreatePayment from './form/CreatePayment.vue';
import EditPayment from './form/EditPayment.vue';
import RefundPayment from './form/RefundPayment.vue';
import PatientPaymentList from './form/PatientPayments.vue';
import PaymentLog from '@/components/action-log/Payment.vue';
import PrepaymentLog from '@/components/action-log/patient/Prepayment.vue';
import ManageMixin from '@/mixins/manage';
import CashboxMixin from '../mixins/cashbox';
import CheckStyleMixin from '@/components/cashier/payments/form/mixins/style';
import PrepaymentEditForm from '@/components/cashier/payments/form/prepayment/FormEdit.vue';
import PaymentRepository from '@/repositories/payment';
import Payment from '@/models/payment';
import CheckPrint from "./form/Print";
import CONSTANTS from '@/constants';
import printer from '@/services/print';

export default {
    mixins: [
        ManageMixin,
        CashboxMixin,
        CheckStyleMixin
    ],
    components: {
        PaymentList,
        BalanceList,
        PrepaymentList,
        PaymentServicesList,
        PaymentAnalysisList,
        ChecksList,
    },
    props: {
        cashier: Object,
        filter: Object,
        cashboxes: {
            type: Array,
            default: () => [],
        },
        checkboxCashboxes: {
            type: Array,
            default: () => [],
        },
        paymentMethods: {
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
            activeTab: 'payments',
            initialAmount: 0,
            totalBalance: 0,
            repo: new PaymentRepository(),
            periodDifference: 0,
            totalPrepaid: 0,
            totalPrepaidRest: 0,
            totalAnalisys: 0,
            totalServices: 0,
            isOnlineCashier: (this.$store.state.user.system_status === CONSTANTS.EMPLOYEE.SYSTEM_STATUSES.ONLINE_PAYMENT),
        }
    },
    watch: {
        cashboxes: {
            handler() {
                this.getBalances();
            },
            deep: true,
        },
        activeTab() {
            this.resetTablesActiveItem();
        },
    },
    mounted() {
        this.getBalances();
    },
    methods: {
        refresh() {
            this.$refs.services.refresh();
            this.$refs.analysis.refresh();
            this.$refs.table.refresh();
            if (this.$refs.prepayments) {
                this.$refs.prepayments.refresh();
            }
        },
        deletionAllowed(){
            if (this.activeItem === null ||
                !this.$canManage('payments.delete', [this.activeItem.clinic_id])) {
                    return true;
            }
            return this.activeItem.items && this.activeItem.items.length > 1;
        },
        resetTablesActiveItem() {
            this.activeItem = null;

            if (this.$refs.table) {
                this.$refs.table.unselectAll();
            }

            if (this.$refs.prepayments) {
                this.$refs.prepayments.unselectAll();
            }
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        getData() {
            let table = this.getManageTable();
            return table ? table.getData() : [];
        },
        loaded() {
            this.getTotal();
            this.getTotalAnalysis();
            this.getTotalServices();
            this.refreshed();
        },
        getTotal() {
            this.repo.getTotal(this.filter).then(response => {
                if (response && response.total != undefined) {
                    this.periodDifference = (response.total);
                } else {
                    this.periodDifference = 0;
                }
            })
        },
        getTotalAnalysis() {
            let analysisFilter = { ...this.filter};
            analysisFilter.service_container = 'analysis_results';
            this.repo.getTotal(analysisFilter).then(response => {
                if (response && response.total != undefined) {
                    this.totalAnalisys = (response.total);
                } else {
                    this.totalAnalisys = 0;
                }
            })
        },
        getTotalServices() {
            let serviceFilter = { ...this.filter};
            serviceFilter.missing_analysis = true;
            this.repo.getTotal(serviceFilter).then(response => {
                if (response && response.total != undefined) {
                    this.totalServices = (response.total);
                } else {
                    this.totalServices = 0;
                }
            })
        },
        getPrepaymentTotal() {
            let totalPrepaid = 0;
            let totalPrepaidRest = 0;
            if (this.$refs.prepayments) {
                let table = this.$refs.prepayments.getManageTable();
                let rows = table.getData();
                rows.forEach(row => {
                    totalPrepaid += Number(row.payment.payed_amount);
                    totalPrepaidRest += (row.used ? 0 : Number(row.amount));
                });
            }
            this.totalPrepaid = totalPrepaid;
            this.totalPrepaidRest = totalPrepaidRest;
        },
        getBalances() {
            this.getInitialAmount();
            this.getTotalBalance();
            if (this.$refs.balance) {
                this.$refs.balance.refresh();
            }
        },
        getTotalBalance() {
            let balance = 0;
            this.cashboxes.forEach((box) => {
                if (box.payment_method.use_cash == true) {
                    balance = balance + this.getBalance(box) + Number(box.initial_amount);
                }
            });
            this.totalBalance = balance.toFixed();
        },
        getInitialAmount() {
            this.initialAmount = 0;
            let cashPrinterBox = this.getCashAndPrinterCashbox(this.cashboxes);

            if (cashPrinterBox) {
                this.initialAmount = cashPrinterBox.initial_amount;
            }
        },
        showPatientPayments(patient) {
            this.$modalComponent(PatientPaymentList, {
                patient_id: patient.id,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    dialog.close();
                    this.getCreatePaymentForm(patient);
                },
            }, {
                header: __('Платежи пациента:') + ' ' + patient.name,
                width: '1270px',
                headerAddon: {
                    component: ToggleModalFilter,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                    },
                },
            });
        },
        createPayment() {
            this.$modalComponent(SearchPatient, {
                showCreateButton: false,
                restrictClinics: this.$isAccessLimited('payments')
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    dialog.close();
                    this.getCreatePaymentForm(patient);
                },
            }, {
                header: __('Добавить платеж / Выбор пациента'),
                width: '1270px',
                headerAddon: {
                    component: ToggleModalFilter,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                    },
                },
            });
        },
        editPayment() {
            this.$modalComponent(EditPayment, {
                item: this.activeItem,
                cashier: this.cashier,
                cashboxes: this.cashboxes,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, payment) => {
                    dialog.close();
                    this.refresh();
                    this.lastActiveItemId = payment.id;
                },
            }, {
                header: __('Редактировать платеж пациента'),
                width: '1100px',
            });
        },
        deletePayment() {
            if (this.activeItem.type == CONSTANTS.PAYMENT.TYPES.EXPENSE) {
                return this.$warning(__('Нельзя удалить возврат'));
            }

            if (this.activeItem.isFiscal) {
                if (!this.$can('payments.temp-delete-clinic')) {
                    return this.$warning(__('Платеж нельзя удалить, фискальная оплата'));
                }
            }

            if (this.isPrepayment()) {
                return this.$warning(__('Нельзя удалить предоплату, оформите возрат'));
            }

            if (this.isPastDeposit()) {
                return this.$warning(__('Нельзя удалить авансовый платеж закрытого периода'));
            }

            this.$confirm(__('Вы уверены что хотите удалить этот платеж пациента?'), () => {
                let payment = new Payment({
                    ...((this.activeItem instanceof Payment) ? this.activeItem.attributes : this.activeItem),
                    is_deleted: true,
                });
                payment.save().then(() => {
                    this.activeItem = null;
                    this.refresh();
                    this.$info(__('Платеж успешно удален'));
                }).catch((e) => {
                    if (e.response) {
                        let error = e.response;
                        if (error.response.status === 422 && error.response.data && error.response.data.error) {
                            this.$error(error.response.data.error);
                        }
                    } else {
                        this.$disaplyErrors(e);
                    }
                    return;
                });
            });
        },
        isPastDeposit() {
            return this.activeItem.is_deposit && this.$moment().isAfter(this.activeItem.created, 'day');
        },
        isPrepayment() {
            return this.activeItem.is_prepayment == true;
        },
        refundPayment() {
            this.$modalComponent(SearchPatient, {
                showCreateButton: false,
                restrictClinics: this.$isAccessLimited('payments')
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    dialog.close();
                    this.getRefundPaymentForm(patient);
                },
            }, {
                header: __('Возврат платежа / Выбор пациента'),
                width: '1270px',
                headerAddon: {
                    component: ToggleModalFilter,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                    },
                },
            });
        },
        getCreatePaymentForm(patient) {
            this.$modalComponent(CreatePayment, {
                patient,
                cashier: this.cashier,
                checkboxCashboxes: this.checkboxCashboxes,
                cashboxes: this.cashboxes,
                activeShift: this.activeShift,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                    this.refresh()
                },
            }, {
                header: __('Добавить платеж пациента:') + ' ' + patient.full_name,
                width: '1270px',
                headerAddon: {
                    component: CreatePaymentHeaderAddon,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                        addDeposit: (dialog) => {
                            dialog.getTopComponent().addDeposit();
                        },
                        addPrepayment: (dialog) => {
                            dialog.getTopComponent().addPrepayment();
                        },
                    },
                },
            });
        },
        getRefundPaymentForm(patient) {
            this.$modalComponent(RefundPayment, {
                patient,
                cashier: this.cashier,
                checkboxCashboxes: this.checkboxCashboxes,
                cashboxes: this.cashboxes,
                isOnlineCashier: this.isOnlineCashier,
                activeShift: this.activeShift,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                    this.refresh()
                },
            }, {
                header: __('Возврат платежа пациента:') + ' ' + patient.full_name,
                width: '1270px',
                headerAddon: {
                    component: ToggleModalFilter,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                    },
                },
            });
        },
        print() {
            let checks = {};
            if (_.isFilled(this.activeItem.check_id)) {
                this.printCheckItems();
            } else {
                let checkId = this.activeItem.check_id;
                checks[checkId] = {
                    checkId,
                    cardNumber: this.activeItem.appointment ? this.activeItem.appointment.card_number : '',
                    services: [this.getPaymentInfo(this.activeItem)],
                    created: this.$moment(this.activeItem.check.created.date),
                    clinic: this.activeItem.clinic,
                };
                this.printChecks(checks);
            }
        },
        printCheckItems() {
            this.getPaymentsByCheckId(this.activeItem.check_id).then(payments => {
                let checks = {};
                payments.forEach((payment) => {
                    let checkId = payment.check_id;
                    if (_.isFilled(checkId)) {
                        if (checks[checkId]) {
                            checks[checkId].services.push(this.getPaymentInfo(payment));
                        } else {
                            checks[checkId] = {
                                checkId,
                                cardNumber: payment.card_number ? payment.card_number : '',
                                services: [this.getPaymentInfo(payment)],
                                created: this.$moment(payment.check.created.date),
                                clinic: payment.clinic,
                                money_reciever: payment.money_reciever_name ? {name: payment.money_reciever_name} : null,
                            };
                        }
                    }
                });
                this.printChecks(checks);
            });
        },
        getPaymentsByCheckId(checkId) {
            return this.repo.fetch({check_id: checkId}, null, ['check','appointment','clinic','money_reciever']).then(response => {
                return Promise.resolve(response.rows);
            });
        },
        printPrepayment() {
            let checks = {};
            let checkId = this.activeItem.payment.check.id;

            checks[checkId] = {
                checkId,
                cardNumber: '',
                services: [{payed_amount : this.activeItem.amount, name : this.activeItem.service_name }],
                created: this.$moment(this.activeItem.payment.check.created_at),
                clinic: this.activeItem.payment.clinic,
            };

            this.printChecks(checks);
        },
        printChecks(checks) {
            if (_.isEmpty(checks)) {
                return;
            }
            printer.printComponent(CheckPrint, {
                isCopy: true,
                checks: checks
            }, null, this.getCheckSettings());
        },
        getPaymentInfo(payment) {
            return {
                payed_amount: Number(payment.payed_amount),
                name: this.getCheckServiceName(payment),
            }
        },
        getCheckServiceName(payment) {
            if (payment.service) {
                return payment.service.analysis_count > 0 ? this.$formatter.listFormat(payment.service.analysis_items) : payment.service.name_ua;
            }
            return __('Попередня оплата за медичні послуги');
        },
        goToPatientCabinet() {
            let routeData = this.$router.resolve({name: 'patient-cabinet', params: {patientId: this.activeItem.patient.id}});
            window.open(routeData.href, '_blank');
        },
        showLog() {
            this.$modalComponent(PaymentLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения платежа'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        editPrepayment() {
            this.$modalComponent(PrepaymentEditForm, {
                item: this.activeItem,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, payment) => {
                    dialog.close();
                },
            }, {
                header: __('Редактировать предоплату'),
                width: '355px',
            });
        },
        checkActiveCheckboxes() {
            return this.checkboxCashboxes.length !== 0
        },
        showPrepaymentLog() {
            this.$modalComponent(PrepaymentLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения предоплаты'),
                width: '900px',
                customClass: 'no-footer',
            });
        }
    },
}
</script>
