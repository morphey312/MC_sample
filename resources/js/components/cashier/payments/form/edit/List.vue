<template>
    <manage-table 
        ref="table"
        :fields="fields"
        :repository="repository"
        :enablePagination="false">
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
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    props: {
        model: Object,
        isCashAndFiscal: Boolean,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: [this.model],
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
                    name: 'amount',
                    title: __('Сумма, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'deposit_types',
                    title: __('Вид платежа'),
                    width: '100px',
                    dataClass: 'text-right',
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
                    name: 'cashbox.name',
                    title: __('Форма оплаты'),
                    width: '100px',
                },
            ],
            doctors: [],
        }
    },
    mounted() {
        this.getDoctors();
    },
    methods: {
        getDoctors() {
            let doctor = new EmployeeRepository();
            let filters = {
                or: [
                {
                    employee_clinic: {
                        clinic: this.model.clinic_id,
                        status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                        position_type: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                    },
                },
                {
                    employee_clinic: {
                        status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                        clinic: this.model.clinic_id,
                    },
                    is_translator: true,
                }],
            };

            doctor.fetchList(filters).then((response) => {
                this.doctors = response;
                let index = this.doctors.findIndex(doctor => doctor.id == this.model.doctor_id);
                if (_.isFilled(this.model.doctor_id) && index === -1) {
                    this.doctors.push({
                        id: this.model.doctor.id,
                        value: this.model.doctor.name,
                    })
                }
            });
        },
    },
}
</script>