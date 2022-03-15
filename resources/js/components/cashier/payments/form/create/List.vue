<template>
    <manage-table
        ref="table"
        v-loading="loading"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        :enable-pagination="false"
        :row-class="rowClass"
        table-uid="create-payments-list"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters"
    >
        <template
            slot="created_at"
            slot-scope="props"
        >
            <form-date
                :entity="props.rowData"
                property="created_at"
                :disabled="!props.rowData.created_at_active"
                css-class="m-0"
            />
        </template>
        <template
            slot="doctor"
            slot-scope="props"
        >
            <form-select
                :entity="props.rowData"
                :options="doctors"
                property="doctor_id"
                label=""
                css-class="m-0"
            />
        </template>
        <template
            slot="name"
            slot-scope="props">
            <span v-if="analysisContainer(props.rowData)">
                <a @click="analysisDetails(props.rowData)">{{ props.rowData.name }}</a>
            </span>
            <span v-else>
                {{ props.rowData.name }}
            </span>
        </template>
        <template
            slot="money_reciever.name"
            slot-scope="props">
            <span v-if="props.rowData.items && props.rowData.items[0].money_reciever">
                {{ props.rowData.items[0].money_reciever.name }}
            </span>
            <span v-else-if="props.rowData.money_reciever">
                {{ props.rowData.money_reciever.name }}
            </span>
            <span v-else>
                {{ "&mdash;" }}
            </span>
        </template>
        <template
            slot="amount"
            slot-scope="props"
        >
            <el-input-number
                v-model="props.rowData.amount"
                controls-position="right"
                :step="1"
                :min="0"
                :max="getMaxAmount(props.rowData)"
                :disabled="props.rowData.multi_cashbox"
                class="text-right input-tiny"
                @change="calcCost(props.rowData)"
            />
        </template>
        <template
            slot="discount"
            slot-scope="props"
        >
            <el-input-number
                v-model="props.rowData.discount"
                controls-position="right"
                :step="1"
                :min="0"
                class="text-right input-tiny"
                @change="calcCost(props.rowData)"
            />
        </template>
        <template
            slot="deposit"
            slot-scope="props"
        >
            <el-input-number
                v-model="props.rowData.deposit"
                controls-position="right"
                :step="1"
                :min="0"
                :max="getMaxDeposit(props.rowData)"
                :disabled="props.rowData.multi_cashbox"
                class="text-right input-tiny"
                @change="verifyDepositAvailability(props.rowData)"
            />
        </template>
        <template
            slot="payment_method"
            slot-scope="props"
        >
            <form-select
                :entity="props.rowData"
                :options="selectCashboxList(props.rowData)"
                :disabled="props.rowData.multi_cashbox"
                property="cashbox_id"
                :error-prefix="`e.${props.rowIndex}`"
                css-class="m-0"
                @changed="verifyCashbox(props.rowData)"
            />
        </template>
        <template
            slot="comment"
            slot-scope="props"
        >
            <el-input
                v-model="props.rowData.comment"
                type="textarea"
                autosize
                :rows="1"
                class="table-textarea"
                :placeholder="__('Добавить текст')"
            />
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import EmployeeRepository from '@/repositories/employee';
import AppointmentServiceRepository from '@/repositories/appointment/service';
import AppointmentService from '@/models/appointment/service';
import PrepaymentRepository from '@/repositories/patient/prepayment';
import AnalysisList from './AnalysisesList.vue';
import CONSTANTS from '@/constants';

export default {
    props: {
        filters: Object,
        model: Object,
        cashboxList: {
            type: Object,
            default: () => {},
        },
        depositAmount: Number,
        invalid: {
            type: Array,
            default: () => [],
        },
        nonFiscalCashboxId: {
            type: [Number, String],
            default: null,
        },
        nonFiscalNonCashCashboxId: {
            type: [Number, String],
            default: null,
        },
        activeShift: {
            type: Object,
            default: () => {},
        },
        checkboxCashboxes: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                return this.getRows(filters, sort, scopes, page, limit);
            }),
            fields: [
                {
                    name: 'created_at',
                    title: __('Дата платежа'),
                    width: '135px',
                    dataClass: 'no-dash',
                },
                {
                    name: 'appointment.date',
                    title: __('Дата записи'),
                    width: '85px',
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'card_number',
                    title: __('Номер карты'),
                    width: '70px',
                    dataClass: 'no-dash',
                },
                {
                    name: 'doctor',
                    title: __('Врач'),
                    width: '185px',
                },
                {
                    name: 'name',
                    title: __('Услуга'),
                    width: '178px',
                },
                {
                    name: 'money_reciever.name',
                    title: __('Получатель денег'),
                    width: '180px',
                },
                {
                    name: 'debt',
                    title: __('Долг, грн'),
                    width: '70px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                {
                    name: 'prepayed',
                    title: __('Предоплата, грн'),
                    width: '90px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                {
                    name: 'expected_payment',
                    title: __('Реком. врачом, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    },
                },
                {
                    name: 'amount',
                    title: __('Сумма, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'deposit',
                    title: __('С аванса, грн'),
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
                    name: 'by_policy',
                    title: __('Полис'),
                    width: '50px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'franchise',
                    title: __('Фр-за, %'),
                    width: '50px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'patient_payment',
                    title: __('К оплате пациентом, грн'),
                    width: '90px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    },
                },
                {
                    name: 'insurance_payment',
                    title: __('К оплате СК, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    },
                },
                {
                    name: 'warranter',
                    title: __('Гарант'),
                    width: '100px',
                    dataClass: 'no-dash',
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    width: '100px',
                },
                {
                    name: 'appointment.comment',
                    title: __('Примечание записи'),
                    width: '100px',
                    dataClass: 'no-dash',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            changedRows: [],
            doctors: [],
            loading: false,
            scopes: [
                'service',
                'appointment',
                'clinic',
            ],
            today: this.$moment().format('YYYY-MM-DD'),
        }
    },
    computed: {
        totalUsedDeposit() {
            let totalUsed = 0;
            let rows = this.getData();

            if (this.getTable() && rows) {
                rows.forEach((item) => {
                    totalUsed += (item.deposit ? item.deposit : 0);
                });
            }
            return totalUsed;
        },
    },
    methods: {
        selectCashboxList(row) {
            if (row.money_reciever) {
                const moneyReciever = this.checkboxCashboxes.find((cashbox) => cashbox.money_reciever_id === row.money_reciever.id);
                if (this.checkboxCashboxes.length && moneyReciever && row.money_reciever && row.money_reciever.id === moneyReciever.money_reciever_id) {
                    return this.cashboxList.all;
                }
            }

            if (row.items && row.items[0].money_reciever) {
                const moneyReciever = this.checkboxCashboxes.find((cashbox) => cashbox.money_reciever_id === row.items[0].money_reciever.id);
                if (this.activeShift && moneyReciever && row.items[0].money_reciever && row.items[0].money_reciever.id === moneyReciever.money_reciever_id) {
                    return this.cashboxList.all;
                }
            }

            return this.cashboxList.nonFiscal;
        },
        analysisContainer(row) {
            return row.container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES;
        },
        verifyDepositAvailability(row) {
            if ((this.depositAmount == 0) || (this.totalUsedDeposit > this.depositAmount)) {
                row.deposit = null;
            } else {
                if (this.nonFiscalCashboxId != null) {
                    row.cashbox_id = this.nonFiscalCashboxId;

                    if (this.activeShift) {
                        row.is_cash = this.cashboxList.all.find((item) => item.id === row.cashbox_id).useCash;
                    } else {
                        row.is_cash = this.cashboxList.nonFiscal.find((item) => item.id === row.cashbox_id).useCash;
                    }
                }
            }
        },
        verifyCashbox(row) {
            this.$nextTick(() => {
                if ((row.deposit > 0) && (row.cashbox_id != this.nonFiscalCashboxId)) {
                    row.cashbox_id = this.nonFiscalCashboxId;
                }

                let selectedCashbox = this.selectCashboxList(row).find((cashbox) => cashbox.id === row.cashbox_id);

                if (selectedCashbox && selectedCashbox.changePaymentDate) {
                    row.created_at_active = true;
                } else {
                    row.created_at_active = false;
                    row.created_at = this.$moment().format('YYYY-MM-DD');
                }

                if (this.activeShift) {
                    row.is_cash = this.cashboxList.all.find((item) => item.id === row.cashbox_id).useCash;
                } else {
                    row.is_cash = this.cashboxList.nonFiscal.find((item) => item.id === row.cashbox_id).useCash;
                }

                if (row.container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES) {
                    row.items.forEach((item) => {
                        item.is_cash = row.is_cash;
                    })
                }
            });
        },
        getRows(filters, sort, scopes, page, limit) {
            let service = new AppointmentServiceRepository();
            this.loading = true;
            return service.fetchPayeableServices(this.filters, sort, scopes, page, limit).then((response) => {
                response.forEach((item) => {
                    if (item.doctor_clinics && item.doctor_id) {
                        let doctorClinic = item.doctor_clinics.find(clinic => clinic.clinic_id === item.clinic_id);
                        if (doctorClinic && doctorClinic.status !== CONSTANTS.EMPLOYEE.STATUSES.WORKING) {
                            item.doctor_id = null;
                        }
                    }
                })

                let clinics = _.uniq(response.map(r => {
                    if (r.appointment != null) {
                        return r.appointment.clinic_id;
                    }
                })
                );
                return this.getPrepayments(this.filters).then((prepayments) => {
                    let repository = new EmployeeRepository();
                    return repository.fetchList(this.getDoctorFilters(clinics, response)).then((list) => {
                        this.doctors = list;
                        this.loading = false;
                        return Promise.resolve({
                            rows: [
                                ... this.prepareMedicinesRows(response, prepayments),
                                ... this.prepareAnalysisRows(response, prepayments),
                            ]
                        });
                    });
                });
            });
        },
        getPrepayments() {
            let repo = new PrepaymentRepository();
            let filters = _.onlyFilled({
                clinic: this.filters.appointment_clinic,
                patient: this.filters.patient,
                used: false,
            });
            return repo.fetchList(filters);
        },
        prepareMedicinesRows(rows, prepayments = []) {
            let medicinesRows = rows.filter((item) => {
                return item.container_type === null || item.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.MEDICINES;
            });
            return this.prepareRows(medicinesRows, prepayments);
        },
        prepareAnalysisRows(rows, prepayments = []) {
            let analysisRows = rows.filter((item) => {
                return item.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES;
            });
            let analysisGrouped = _.groupBy(analysisRows,'appointment_id');
            let preparedRow = [];
            Object.keys(analysisGrouped).forEach(key => {
                let analysis = [];
                let analisysWrapper = _.first(analysisGrouped[key]);
                let analisysRow = this.createAnalysisRow(analisysWrapper);
                analysisGrouped[key].forEach((item) => {
                    this.castAnalystRow(item,analisysRow,prepayments);
                    analysis.push(item);
                });
                analisysRow.items = this.prepareRows(analysis, prepayments);
                preparedRow.push(analisysRow);
            });

            return preparedRow;

        },
        castAnalystRow(row, analisysRow, prepayments = []) {
            let expectedPaymentData = this.getServiceExpectedPayment(row);
            let prepayment = prepayments.find(item => {
                return item.service_id == row.service_id && item.clinic_id == row.clinic_id;
            });
            if (prepayment && debt >= prepayment.amount) {
                row.prepayed = Number(prepayment.amount);
                row.prepayment_id = prepayment.id;
            }
            let debt = this.getDebtAttribute(row);

            analisysRow.debt += debt;
            row.patient_payment = expectedPaymentData.patient;
            row.insurance_payment = expectedPaymentData.insurance;
            row.deposit = null;

        },
        getSummaryPrepayment(prepayments) {
            let amount = 0;
            prepayments.forEach((prepayment) => {
                amount += Number(prepayment.amount);
            });
            return amount;
        },
        getIdPrepayment(prepayments) {
            return prepayments.map((prepayment) => prepayment.id);
        },
        prepareRows(rows, prepayments = []) {
            return _.orderBy(rows, ['row.appointment.date'], ['desc'])
                .map((row) => {
                    let expectedPaymentData = this.getServiceExpectedPayment(row);
                    let prepayment = prepayments.filter(item => {
                        return item.service_id == row.service_id && item.clinic_id == row.clinic_id;
                    });
                    let debt = this.getDebtAttribute(row);
                    if (prepayment && debt >= this.getSummaryPrepayment(prepayment)) {
                        row.prepayed = Number(this.getSummaryPrepayment(prepayment));
                        row.prepayment_id = this.getIdPrepayment(prepayment);
                    }

                    row.cashbox_id = null;
                    row.expected_payment = _.isVoid(row.expected_payment) ? 0 : row.expected_payment;
                    row.patient_payment = expectedPaymentData.patient;
                    row.insurance_payment = expectedPaymentData.insurance;
                    row.doctor_id = row.doctor_id;
                    row.is_cash = false;
                    row.debt = debt;
                    row.created_at_active = false;
                    row.created_at = this.today;
                    return row;
                });
        },
        createAnalysisRow(service) {
            return new AppointmentService({
                service_id: service.id,
                container_type: CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES,
                card_assignment_id: null,
                name: service.name,
                clinic: service.clinic,
                clinic_id: service.clinic_id,
                card_number: service.card_number,
                doctor_id: service.doctor_id,
                appointment: service.appointment,
                created_at: this.today,
                cashbox_id: null,
                expected_payment: 0,
                patient_payment: 0,
                insurance_payment: 0,
                debt: 0,
                multi_cashbox: false,
                created_at_active: false,
            });
        },
        getServiceExpectedPayment(row) {
            let expected = {
                patient: 0,
                insurance: 0,
            };

            if (row.by_policy) {
                expected.patient = Math.round(row.franchise ? (Number(row.cost) / 100 * Number(row.franchise)) : row.cost);
                expected.insurance = Number(row.cost) - Number(expected.patient);
            }
            return expected;
        },
        getDebtAttribute(row) {
            let cost = Number(row.cost);
            return _.isVoid(row.paid) ? cost : (cost - Number(row.paid));
        },
        getDoctorFilters(clinic, rows) {
            return {
                or: [
                    {
                        employee_clinic: {
                            clinic,
                            status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                            position_type: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                        },
                    },
                    {
                        employee_clinic: {
                            status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                            clinic,
                        },
                        is_translator: true,
                    },
                    {
                        id: this.getDoctorIds(rows),
                    }],
            };
        },
        getDoctorIds(rows) {
            return rows.reduce((list, row) => {
                if (_.isFilled(row.doctor_id)) {
                    list.push(row.doctor_id);
                }
                return list;
            }, []);
        },
        calcCost(row) {
            let amount = row.amount ? Number(row.amount) : 0;
            if (_.isFilled(row.discount)) {
                row.payed_amount = amount - (amount / 100 * Number(row.discount));
            } else {
                row.payed_amount = amount;
            }
            this.$emit('calc-total');
        },
        getMaxAmount(row) {
            let amount = row.debt
            if (row.prepayed) {
                amount = row.debt - row.prepayed;
            }
            if (row.deposit) {
                amount = amount - row.deposit;
            }
            return Number(amount);
        },
        getMaxDeposit(row) {
            let deposit = row.debt
            if (row.prepayed) {
                deposit = row.debt - row.prepayed;
            }
            if (row.amount) {
                deposit = deposit - row.amount;
            }
            return Number(deposit);
        },
        rowClass(row) {
            if (this.isInvalid(row)) {
                return ['changed', 'error'];
            }
        },
        isInvalid(row) {
            return this.invalid.findIndex(item => {
                return item.service_id == row.id;
            }) != -1;
        },
        getTable() {
            return this.$refs.table;
        },
        refresh() {
            return this.getTable().refresh();
        },
        getData() {
            let table = this.getTable();
            return table ? table.getData() : [];
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
        mergeAnalisys(row, list) {
            row.amount = _.sum(_.map(list, (item) => { return item.payed_amount }));
            row.payed_amount = _.sum(_.map(list, (item) => { return item.payed_amount }));
            row.deposit = _.sum(_.map(list, (item) => { return item.deposit }));
            row.items = list;
            if (row.payed_amount > 0 || row.deposit > 0) {
                row.multi_cashbox = true;
                row.cashbox_id = null;
            } else{
                row.multi_cashbox = false;
            }
        },
        changeAnalysisDeposit(row, list) {
            row.deposit = _.sum(_.map(list, (item) => { return item.deposit }));
        },
        analysisDetails(row) {
            this.$modalComponent(AnalysisList, {
                appointment_id: row.appointment_id,
                analisys: row.items,
                cashboxList: this.selectCashboxList(row),
                total: row.debt,
                depositAmount: this.depositAmount,
                totalUsedDeposit: this.totalUsedDeposit,
                nonFiscalCashboxId: this.nonFiscalCashboxId,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    this.mergeAnalisys(row, list);
                    this.$emit('calc-total');
                    dialog.close();
                },
                changeDeposit: (dialog, list) => {
                    this.changeAnalysisDeposit(row, list);
                },
            }, {
                header: __('Разнести деньги за анализы в записи'),
                width: '1200px',
                customClass: 'padding-0',
            });
        }
    },
}
</script>
