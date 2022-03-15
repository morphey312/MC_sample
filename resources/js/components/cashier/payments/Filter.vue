<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    property="clinic"
                    :clearable="true"
                    :filterable="true"
                    :label="__('Клиника')" />
                <filter-patient
                    :entity="filter"
                    patient-name-prop="patient_name"
                    patient-id-prop="patient"
                    :label="__('ФИО пациента')" />
                <form-select
                    :entity="filter"
                    :options="doctors"
                    property="doctor"
                    :clearable="true"
                    :filterable="true"
                    :label="__('Врач')" />
                <form-select
                    :entity="filter"
                    :options="money_recievers"
                    property="checkbox_money_reciever"
                    :clearable="true"
                    :filterable="true"
                    :label="__('Получатель денег')" />
            </el-col>
            <el-col :span="6">
                <form-input-search
                    :entity="filter"
                    property="patient_card_number"
                    :clearable="true"
                    :label="__('Номер карты')" />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    property="card_specialization"
                    :label="__('Специализация карты')" />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    property="doctor_specialization"
                    :label="__('Специализация врача')" />
                <form-select
                    :entity="filter"
                    :options="money_reciever_cashboxes"
                    property="money_reciever_cashbox"
                    :clearable="true"
                    :filterable="true"
                    :label="__('Касса Checkbox')" />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="period"
                    :label="__('Период')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="createdStart"
                            :editable="false"
                            :clearable="true" />
                        <form-date
                            :entity="filter"
                            property="createdEnd"
                            :editable="false"
                            :clearable="true" />
                    </div>
                </form-row>
                <form-select
                    :entity="filter"
                    :options="paymentMethods"
                    property="payment_method"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    :collapse-tags="true"
                    :label="__('Форма оплаты')"
                />
                <form-select
                    :entity="filter"
                    :options="cashiers"
                    property="cashier"
                    :clearable="true"
                    :filterable="true"
                    :label="__('Кассир')"
                />
            </el-col>
            <el-col :span="6">
                <form-switch
                    :entity="filter"
                    :options="payment_types"
                    property="payment_type"
                    :label="__('Тип платежа')" />
                <form-switch
                    :entity="filter"
                    :options="payment_kinds"
                    property="payment_kind"
                    :label="__('Вид платежа')" />
                <form-select
                    :entity="filter"
                    :options="payment_destinations"
                    property="payment_destination"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    :collapse-tags="true"
                    :label="__('Назначение платежа')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>
<script>
import FilterMixin from '@/mixins/filter';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';
import MoneyRecieverRepository from "@/repositories/clinic/money-reciever";
import MoneyRecieverCashbox from "@/repositories/money-reciever-cashbox";

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        cashier: Object,
        paymentMethods: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('payments'),
            }),
            doctors: new EmployeeRepository({
                limitClinics: this.$isAccessLimited('payments'),
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR},
            }),
            specializations: new SpecializationRepository({
                filters: {status: 1},
                limitClinics: this.$isAccessLimited('payments'),
            }),
            payment_destinations: new PaymentDestinationRepository({
                filters: {
                    disabled: 0
                }
            }),
            money_recievers: new MoneyRecieverRepository(),
            money_reciever_cashboxes: new MoneyRecieverCashbox(),
            cashiers: [],
            payment_types: this.setPaymentTypes(),
            payment_kinds: this.setPaymentKinds(),
        }
    },
    watch:{
        ['filter.clinic']: {
            handler() {
                this.specializations.setFilters(this.getSpecializationFilters());
                this.payment_destinations.setFilters(this.getPaymentDestinationsFilters());
            }
        },
        ['filter.createdEnd']: {
            handler() {
                this.specializations.setFilters(this.getSpecializationFilters());
            }
        },
        ['filter.createdStart']: {
            handler() {
                this.specializations.setFilters(this.getSpecializationFilters());
            }
        }
    },
    mounted() {
        this.getCashiers();
    },
    methods: {
        changed(filters) {
            if (this.$isAccessLimited('payments') && filters.clinic === null) {
                filters.clinic = this.cashier.clinic_id;
            }
            this.$emit('changed', this.makeFilters(filters));
        },
        initFilter(fromState = {}){
            let today = this.$moment().format('YYYY-MM-DD');

            this.filter = {
                clinic: this.cashier.clinic_id,
                patient: null,
                patient_name: null,
                doctor: null,
                cashier: null,
                patient_card_number: null,
                card_specialization: null,
                doctor_specialization: null,
                createdStart: today,
                createdEnd: today,
                payment_method: [],
                payment_type: null,
                payment_kind: null,
                payment_destination: [],
                ...fromState,
            };
        },
        setPaymentTypes() {
            return [{ id: null, value: __('Все')}, ...this.$handbook.getOptions('payment_type')];
        },
        setPaymentKinds() {
            return [
                {
                    id: null,
                    value: __('Все')
                },
                {
                    id: CONSTANTS.PAYMENT.KINDS.HAS_APPOINTMENT,
                    value: this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.HAS_APPOINTMENT)
                },
                {
                    id: CONSTANTS.PAYMENT.KINDS.DEPOSIT,
                    value: this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.DEPOSIT)
                },
            ];
        },
        getCashiers() {
            if (this.cashier !== null) {
                this.cashiers.push({
                    id: this.cashier.id,
                    value: this.cashier.full_name,
                });
                this.filter.cashier = this.cashier.id;
            }
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                is_in_payments: {
                    from: this.filter.createdStart,
                    to: this.filter.createdEnd,
                    clinic: [this.filter.clinic]
                },
                status: 1,
                clinic: this.filter.clinic,
            });
        },
        getPaymentDestinationsFilters() {
            return _.onlyFilled({
                disabled: 0,
                clinic: this.filter.clinic,
            });
        },
    }
}
</script>
