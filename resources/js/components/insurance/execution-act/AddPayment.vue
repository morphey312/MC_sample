<template>
    <model-form :model="act" v-loading="loading">
        <el-row :gutter="20">
            <el-col :span="12">
               <form-input
                property="amount"
                :entity="form"
                :label="__('Сумма, грн')"
            />
            </el-col>
            <el-col :span="12">
                <form-date
                    :entity="form"
                    property="payment_date"
                    :options="pickerOptions"
                    :label="__('Дата')"
                    />
            </el-col>
        </el-row>
        <slot name="buttons">
            <div class="form-footer text-right">
                <el-button @click="close">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    @click="create">
                    {{ __('Сохранить') }}
                </el-button>
            </div>
        </slot>
    </model-form>
</template>

<script>
import ExportActMixin from './mixin/act-requisites';
import AppointmentServiceRepository from '@/repositories/appointment/service';
import BatchRequest from '@/services/batch-request';
import CONSTANTS from '@/constants';
import Payment from '@/models/payment';
import CashboxMixin from '@/components/cashier/mixins/cashbox';
import Employee from '@/models/employee';
import depositPayments from './generator/depositPayments';
import ServiceGenerator from './generator/service';
import FileSaver from 'file-saver';

export default {
     mixins: [
        ExportActMixin,
        CashboxMixin,
    ],
    props: {
        act: Object,
    },
    data() {
        return {
            form: {
                amount: 0,
                payment_date : this.$moment().format('YYYY-MM-DD'),
            },
            pickerOptions: {
                disabledDate: this.checkDisabledDate,
                firstDayOfWeek: 1,
            },
            batchRequest: new BatchRequest('/api/v1/payments/batch'),
            paymentDate: null,
            loading: false,
            analysisPaymentDestination: null,
            medicinePaymentDestination: null,
            isCashier: this.$store.state.user.isCashier,
            cashboxes: [],
            paymentDestinations: [],

        }
    },
    mounted() {
        this.initCashier();
        this.getCashboxes();
    },
    methods: {
        close() {
            this.$emit('close');
        },
        correctDate() {
            let today = this.$moment().format('YYYY-MM-DD');
            return this.form.payment_date > today
            || this.form.payment_date < this.$moment(this.act.created).format('YYYY-MM-DD');
        },
        create() {
            if (this.correctDate()) {
                this.$error(__('Дата должна быть не меньшей чем дата Формирования акта'));
            }else if(parseFloat(this.form.amount) != this.act.amount) {
                this.$error(__('Внесенная сумма не совпадает с суммой акта'));
            }else {
                 this.$confirm(
                    __('Акт полностью погашен, разнести платежи по страховым пациентам?'),
                    () => this.confirmCreatePayments(),
                {
                    confirmBtnText: __('Разнести платежи'),
                    cancelBtnText: __('Отменить'),
                }
            );
            }
        },
        checkDisabledDate(date) {
            return this.$moment(date).isAfter(this.$moment(), "day");
        },
        confirmCreatePayments() {
             this.$confirm(
                    __('Вы уверены что хотите разнести платежи? Данное действие нельзя отменить'),
                    () => this.createPayments(this.act.id,this.form.payment_date),
                {
                    confirmBtnText: __('Ок'),
                    cancelBtnText: __('Отменить'),
                }
                 );
        },
        initCashier() {
            if (this.isCashier) {
                this.cashier = new Employee({
                    id: this.$store.state.user.employee_id,
                    full_name: this.$store.state.user.full_name,
                    clinic_id: this.$store.state.user.cashierClinicId,
                    is_cashier: this.$store.state.user.isCashier,
                });
            } else {
                this.$error(__('Пользователь не является кассиром!'));
            }
        },
        createPayments(id, date) {
            this.loading = true;
            this.paymentDate = date;
            this.getActServices(id).then(services => {
                 this.preparePayments(services);
            });
        },
        getActServices(id) {
            let repo = new AppointmentServiceRepository();
            return repo.fetchInsuranceExportList({insurance_act: id})
                .then((response) => {
                    return Promise.resolve(response.rows);
                });
        },
        preparePayments(services) {
            let deposits = [];
            let unValidByDoctorType = [];

            services.forEach((row) => {
                if (row.doctor_id === null) {
                    unValidByDoctorType.push(row);
                } else {
                    let payment = new Payment(this.getPaymentData(row));
                    this.batchRequest.create(payment);
                    if(this.needAddDeposit(row)) {
                        deposits.push(row);
                        let depositAmount = this.calcDeposit(row);
                        let deposit = new Payment({
                            patient_id: row.patient_id,
                            cashier_id: this.cashier.id,
                            type: CONSTANTS.PAYMENT.TYPES.INCOME,
                            clinic_id: row.clinic_id,
                            cashbox_id: this.getNonCashNonFiscalCahboxId(this.cashboxes, 5),
                            is_deposit: true,
                            amount: depositAmount,
                            payed_amount: depositAmount,
                        });
                        this.batchRequest.create(deposit);
                    }
                }
            });
            this.batchRequest.submit().then((result) => {
                if (result.failure.length !== 0) {
                    this.$error(__('Не удалось сохранить некоторые данные'));
                } else {
                    let actStatus = CONSTANTS.INSURANCE_ACT.STATUSES.PAYED;
                    if (deposits.length > 0) {
                        this.exportDeposits(deposits);
                    }
                    if (unValidByDoctorType.length > 0) {
                        actStatus = CONSTANTS.INSURANCE_ACT.STATUSES.PARTLY;
                        this.exportUnvalidServices(unValidByDoctorType);
                    }

                    this.updateActStatus(actStatus);
                    this.$info(__('Данные успешно обновлены'));
                    this.refresh();
                }
            }).catch((error) => {
                if (error.invalid) {
                    this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
                }
            });
        },
        calcDeposit(row) {
            return  (Number(row.cost) - Number(row.act_service.insurance_payment) - Number(row.payed)) * -1;
        },
        needAddDeposit(row) {
            let awaitingAmount = Number(row.act_service.insurance_payment);
            let currentAmount = Number(row.cost) - Number(row.payed);
            if(awaitingAmount > currentAmount) {
                return true;
            }
            return false;
        },
        updateActStatus(status) {
            this.act.status = status;
            this.act.payment_status = status;
            this.act.payment_date = this.form.payment_date;
            this.act.save().then(() => {
                 this.$emit('added');
            });
        },
        exportDeposits(rows) {
            depositPayments(rows).then((book) => {
                book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), __('Авансы пациентам (страховые)') + '.xlsx');
                }).catch((err) => {
                    this.$error(__('Не удалось сохранить файл'));
                    this.loading = false;
                    });
                });
        },
        exportUnvalidServices(services) {
            ServiceGenerator(services).then((book) => {
                book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), __('Услуги которые нужно провести в ручную по № {act_number} акту', {act_number: this.act.number }) + '.xlsx');
                }).catch((err) => {
                    this.$error(__('Не удалось сохранить файл'));
                    this.loading = false;
                });
            });
        },
        getPaymentData(row) {
            return {
                amount: this.calcPayedAmount(row),
                payed_amount: this.calcPayedAmount(row),
                service_id: row.id,
                discount: row.discount,
                cashbox_id: this.getNonCashNonFiscalCahboxId(this.cashboxes, 5),
                doctor_id: row.doctor_id,
                clinic_id: row.clinic_id,
                cashier_id: this.cashier.id,
                appointment_id: row.appointment_id,
                payment_destination_id: row.payment_destination_id,
                patient_id: row.patient_id,
                type: CONSTANTS.PAYMENT.TYPES.INCOME,
                created_at: this.paymentDate,
            };
        },
        calcPayedAmount(row) {
            let debt = 0;
            row.cost = Number(row.cost);
            if (row.franchise == 0) {
                debt = row.cost;
            } else {
                debt = row.cost - (row.cost / 100 * Number(row.franchise));
            }
            return debt;
        },
        getCashboxes() {
            if (this.cashier === null) {
                return;
            }
            this.cashier.fetchCashboxes({cashbox_clinic: this.cashier.clinic_id, enabled_method: true}).then((response) => {
                this.cashboxes = response;
            });
        },
    }
}

</script>
