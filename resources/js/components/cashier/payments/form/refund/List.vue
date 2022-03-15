<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        :enablePagination="false"
        table-uid="refund-list"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot="doctor"
            slot-scope="props">
            <form-select
                :entity="props.rowData"
                :options="doctors"
                property="doctor_id"
                label=""
                cssClass="m-0" />
        </template>
        <template
            slot="service_name"
            slot-scope="props">
            <span v-if="props.rowData.container_type === 'analysis_results'">
                {{ getAnalysisNameList(props.rowData) }}
            </span>
            <span v-else>
                {{ props.rowData.name }}
            </span>

        </template>
        <template
            slot="amount"
            slot-scope="props">
            <el-input-number
                v-model="props.rowData.amount"
                controls-position="right"
                :step="1"
                :min="0"
                :max="getRefundMax(props.rowData)"
                class="text-right input-tiny" />
        </template>
        <template
            slot="cashbox"
            slot-scope="props"
        >
            {{ getCashboxName(props.rowData) }}
        </template>
        <template
            slot="is_cash"
            slot-scope="props"
        >
            <el-checkbox
                style="padding-left: 40%"
                v-model="props.rowData.is_cash"
            />
        </template>
        <template
            slot="payment_method"
            slot-scope="props">
            <form-select
                :entity="props.rowData"
                :options="selectCashboxList(props.rowData)"
                property="cashbox_id"
                :error-prefix="`e.${props.rowIndex}`"
                label=""
                cssClass="m-0"
                @changed="verifyCashbox(props.rowData)" />
        </template>
        <template
            slot="comment"
            slot-scope="props">
            <el-input
                type="textarea"
                autosize
                :rows="1"
                class="table-textarea"
                :placeholder="__('Добавить текст')"
                v-model="props.rowData.comment"/>
        </template>
        <template
            slot="is_technical"
            slot-scope="props">
            <el-checkbox v-model="props.rowData.is_technical" />
        </template>
    </manage-table>
</template>
<script>

import PrepaymentRepository from '@/repositories/patient/prepayment';
import ProxyRepository from '@/repositories/proxy-repository';
import EmployeeRepository from '@/repositories/employee';
import AppointmentServiceRepository from '@/repositories/appointment/service';
import CONSTANTS from '@/constants';

export default {
    props: {
        model: Object,
        filters: Object,
        cashier: Object,
        cashboxes: Array,
        cashboxList: {
            type: Object,
            default: () => {},
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
            repository: new ProxyRepository(() => {
                return this.getRows();
            }),
            fields: [
                {
                    name: 'appointment.date',
                    title: __('Дата записи'),
                    width: '85px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'payment.created_at',
                    title: __('Дата оплаты'),
                    width: '130px',
                    formatter: (val) => {
                        return this.$formatter.datetimeFormat(val);
                    },
                },
                {
                    name: 'appointment.card_number',
                    title: __('Номер карты'),
                    width: '70px',
                },
                {
                    name: 'doctor',
                    title: __('Врач'),
                    width: '160px',
                },
                {
                    name: 'service_name',
                    title: __('Услуга'),
                    width: '180px',
                },
                {
                    name: 'money_reciever.name',
                    title: __('Получатель денег'),
                    width: '190px',
                },
                {
                    name: 'cashbox',
                    title: __('Касса'),
                    width: '100px',
                },
                {
                    name: 'paid',
                    title: __('Сумма, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'deposit_types',
                    title: __('Вид платежа'),
                    width: '100px',
                    formatter: (val) => {
                        if (val && val.is_deposit == true) {
                            return val.is_prepayment
                                ? this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.PREPAYMENT)
                                : this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.DEPOSIT);
                        }
                        return this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.HAS_APPOINTMENT);
                    },
                },
                {
                    name: 'amount',
                    title: __('Возврат, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'payment_method',
                    title: __('Форма оплаты'),
                    width: '100px',
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    width: '100px',
                },
                {
                    name: 'is_technical',
                    title: __('Тех.'),
                    width: '60px',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            doctors: [],
            scopes: [
                'appointment',
                'cashbox',
                'payment_destination',
                'doctor',
                'cashier',
                'service',
            ],
            availableDeposit: 0,
        }
    },
    mounted() {
        this.getDoctors();
    },
    methods: {
        selectCashboxList(row) {
            const moneyReciever = this.checkboxCashboxes.find((cashbox) => row.money_reciever && cashbox.money_reciever_id === row.money_reciever.id);
            const checkMoneyReciever = moneyReciever && row.money_reciever && row.money_reciever.id === moneyReciever.money_reciever_id;
            const checkDeposit = row.deposit_types && row.deposit_types.is_deposit;
            const checkPrepayment = row.is_prepayment && !row.used;

            if (this.activeShift && (checkMoneyReciever || checkDeposit || checkPrepayment)) {
                return this.cashboxList.all;
            }

            return this.cashboxList.nonFiscal;
        },
        getAnalysisNameList(row) {
            let analysis = row.analysis_items.map(item => {
                return item.name;
            });
            return this.$formatter.listFormat(analysis);
        },
        getRows() {
            return this.getPatientDepositAmount().then(() => {
                return this.getPayments();
            })
        },
        getCashboxName(service) {
            if (service.money_reciever) {
                let cashbox = this.checkboxCashboxes.find(item => item.money_reciever_id === service.money_reciever.id);
                return cashbox ? cashbox.money_reciever_cashbox_name : '-';
            }
        },
        getPatientDepositAmount() {
            return this.model.fetch(['accounts']).then(() => {
                if (this.isOnlineCashier) {
                    this.availableDeposit = Number(this.model.getClinicDepositBalance(this.filters.appointment_clinic));
                } else {
                    this.availableDeposit = Number(this.model.getClinicDepositBalance(this.cashier.clinic_id));
                }
                return Promise.resolve();
            });
        },
        getServicePayments() {
            let service = new AppointmentServiceRepository();
            return service.fetchPayments(this.filters);
        },
        getPrepayments() {
            let repo = new PrepaymentRepository();
            let filters = {
                patient: this.filters.patient,
                clinic: this.filters.appointment_clinic,
                used: false,
            }
            return repo.fetchList(filters);
        },
        getPayments() {
            return Promise.all([
                this.getPrepayments(),
                this.getServicePayments(),
            ]).then(response => {
                let rows = [
                    ...this.getPatientDepositRow(),
                    ...this.preparePrepayments(response[0]),
                    ...this.prepareRows(response[1]),
                ];
                return Promise.resolve({rows});
            });
        },
        getPatientDepositRow() {
            if (this.availableDeposit > 0) {
                return [{
                    paid: this.availableDeposit,
                    appointment_id: null,
                    clinic_id: this.isOnlineCashier ? this.filters.appointment_clinic : this.cashier.clinic_id,
                    is_technical: false,
                    deposit_types: {
                        is_deposit: true,
                    },
                }];
            }
            return [];
        },
        prepareRows(rows) {
            return _.orderBy(rows, [row => (row.appointment ? row.appointment.date : null)], ['desc'])
                .map((row) => {
                    row.cashbox_id = null;
                    row.is_technical = false;
                    return row;
                });
        },
        preparePrepayments(rows) {
            return rows.map(row => {
                row.prepayment_id = row.id;
                row.paid = row.amount;
                row.id = null;
                row.amount = null;
                row.is_prepayment = true;
                row.is_technical = false;
                row.deposit_types = {
                    is_deposit: true,
                    is_prepayment: true,
                };
                return row;
            });
        },
        getDoctors() {
            let doctor = new EmployeeRepository();
            let filters = {
                or: [
                    {
                        employee_clinic: {
                            clinic: this.$store.state.user.clinics,
                            status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                            position_type: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                        },
                    },
                    {
                        employee_clinic: {
                            clinic: this.$store.state.user.clinics,
                            status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                        },
                        is_translator: true,
                    },
                ],
            };

            doctor.fetchList(filters).then((response) => {
                this.doctors = response;
            });
        },
        getRefundMax(row) {
            if (_.isFilled(row.service_id)) {
                return Number(row.paid);
            }
            return Number(this.availableDeposit);
        },
        getTable() {
            return this.$refs.table;
        },
        refresh() {
            return this.getTable().refresh();
        },
        getData() {
            return this.getTable().getData();
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        verifyCashbox(row) {
            this.$nextTick(() => {
                if (row.money_reciever) {
                    const moneyReciever = this.checkboxCashboxes.find((cashbox) => cashbox.money_reciever_id === row.money_reciever.id);
                    if (this.activeShift && moneyReciever && row.money_reciever && row.money_reciever.id === moneyReciever.money_reciever_id) {
                        row.is_cash = this.cashboxList.all.find((item) => item.id === row.cashbox_id).useCash;
                    }
                } else {
                    row.is_cash = this.cashboxList.nonFiscal.find((item) => item.id === row.cashbox_id).useCash;
                }
            });
        },
    },
}
</script>
