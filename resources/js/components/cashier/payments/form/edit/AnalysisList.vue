<template>
    <manage-table 
        ref="table"
        :fields="fields"
        :repository="repository"
        :enablePagination="false">
        <template
            slot="created_at"
            slot-scope="props">
            <form-date
                :entity="props.rowData" 
                property="created_at"
                cssClass="m-0" />
        </template>
        <template
            slot="doctor"
            slot-scope="props">
            <form-select
                :entity="props.rowData" 
                :options="doctors"
                :disabled="props.rowData.is_deposit == true"
                property="doctor_id"
                label=""
                cssClass="m-0" />
        </template>
        <template
            slot="service.name"
            slot-scope="props">
            <form-select
                :entity="props.rowData" 
                :options="debtServices"
                :disabled="props.rowData.is_deposit == true"
                property="service_id"
                label=""
                cssClass="m-0"/>
        </template>
        <template
            slot="payed_amount"
            slot-scope="props">
            <el-input-number
                v-model="props.rowData.payed_amount"
                controls-position="right"
                :disabled="true"
                :step="1"
                :min="0"
                :max="Number(props.rowData.payed_amount)"
                class="text-right input-tiny" />
        </template>
        <template
            slot="cashbox"
            slot-scope="props">
            <form-select
                :entity="props.rowData" 
                :options="cashboxList"
                property="cashbox_id"
                :disabled="isPastPayment(props.rowData)"
                label=""
                cssClass="m-0" />
        </template>
        <template
            slot="comment"
            slot-scope="props">
            <form-input
                :entity="props.rowData"
                property="comment"
                cssClass="m-0"
            />
        </template>
        <template
            slot="is_technical"
            slot-scope="props">
            <el-checkbox v-model="props.rowData.is_technical" />
        </template>
        <template slot="footer-top">
            <div class="buttons">
                <el-button
                    @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                     @click="save"
                     :disabled="loading">
                    {{ __('Сохранить') }}
                </el-button>
            </div>
        </template>
       
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import EmployeeRepository from '@/repositories/employee';
import AppointmentServiceRepository from '@/repositories/appointment/service';
import Payment from '@/models/payment';
import BatchRequest from '@/services/batch-request';
import CONSTANTS from '@/constants';

export default {
    props: {
        item: Object,
        isCashAndFiscal: Boolean,
        cashboxList: {
            type: Array,
            default: () => [],
        },
        isOnlineCashier: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            batchRequest: new BatchRequest('/api/v1/payments/batch'),
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.item.items
                });
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
                ...(this.isOnlineCashier ? [
                {
                    name: 'created_at',
                    title: __('Дата платежа'),
                    width: '85px',
                },
                ] : []),
                {
                    name: 'card_number',
                    title: __('Номер карты'),
                    width: '70px',
                },
                {
                    name: 'doctor',
                    title: __('Врач'),
                    width: '185px',
                },
                {
                    name: 'service.name',
                    title: __('Услуга'),
                },
                {
                    name: 'payed_amount',
                    title: __('Сумма, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'amount',
                    title: __('Оплачено, грн'),
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
                    }
                },
                {
                    name: 'cashbox',
                    title: __('Форма оплаты'),
                    width: '100px',
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                    width: '100px',
                },
            ],
            doctors: [],
            debtServices: [],
            loading: false,
        }
    },
    mounted() {
        this.getServices();
        this.getDoctors();
    },
    methods: {
        isPastPayment(row) {
            return this.$moment().isAfter(row.created_at, 'day');
        },
        getServices() {
            let service = new AppointmentServiceRepository();
            this.loading = true;
            return service.fetchPayeableServices(this.getFilters()).then((response) => {
                this.debtServices = [
                    ...this.getItemsServices(),
                    ...response.map(row => this.getRow(row))
                ];
                this.loading = false;
            });
        },
        getItemId() {
            if (this.item.items) {
                return this.item.items.map(res => res.id);
            }
            return this.item.id;
        },
        getItemsServices() {
            let services = [];
            this.item.items.forEach(row => {
                services.push({
                        id: row.service_id,
                        value: this.getValueByServiceType(row),
                        appointment_id: row.appointment_id,
                        payment_destination_id: row.payment_destination_id,
                    });
            });

            return services;
        },
        getValueByServiceType(row) {
            if (row.service && (row.service.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES)) {
                return `${this.$formatter.listFormat(row.service.analysis_items)} ${row.appointment_id ? this.$formatter.dateFormat(row.appointment.date) : ''}`;
            }
            return `${(row.service ? row.service.name : '')} ${row.appointment_id ? this.$formatter.dateFormat(row.appointment.date) : ''}`;
        },
        getRow(row) {
            return {
                id: row.id,
                value: `${row.name} ${row.appointment_id ? this.$formatter.dateFormat(row.appointment.date) : ''}`,
                appointment_id: row.appointment_id,
                cost: row.cost,
                payment_destination_id: row.payment_destination_id,
            };
        },
        getFilters() {
            return {
                appointment_clinic: this.item.clinic_id,
                patient: this.item.patient_id,
            }
        },
        getDoctors() {
            let doctor = new EmployeeRepository();
            let filters = {
                or: [
                {
                    employee_clinic: {
                        clinic: this.item.clinic_id,
                        status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                        position_type: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                    },
                },
                {
                    employee_clinic: {
                        status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                        clinic: this.item.clinic_id,
                    },
                    is_translator: true,
                }],
            };
            doctor.fetchList(filters).then((response) => {
                this.doctors = response;
                let index = this.doctors.findIndex(doctor => doctor.id == this.item.doctor_id);
                if (_.isFilled(this.item.doctor_id) && index === -1) {
                    this.doctors.push({
                        id: this.item.doctor.id,
                        value: this.item.doctor.name,
                    })
                }
            });
        },
        save() {
            this.loading = true;
            this.batchRequest.reset();
            this.item.items.forEach(item => {
                this.batchRequest.update(new Payment(item));
            });
            this.batchRequest.submit().then((result) => { 
                this.$emit('saved');
            });
        },
        cancel() {
            this.$emit('cancel');
        },
    },
}
</script>
