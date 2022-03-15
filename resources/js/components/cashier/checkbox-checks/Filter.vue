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
                    :clearable="$can('payments.access')"
                    :filterable="true"
                    :multiple="true"
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
                    :multiple="true"
                    :label="__('Врач')" />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    property="patient_card_number"
                    :clearable="true"
                    :label="__('Номер карты')" />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="card_specialization"
                    :label="__('Специализация карты')" />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="doctor_specialization"
                    :label="__('Специализация врача')" />
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
                    property="cashbox_payment_method"
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
import PaymentMethodRepository from '@/repositories/payment-method';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        primaryClinicId: {
            type: Array,
            default: () => [],
        }
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
                limitClinics: this.$isAccessLimited('payments'),
            }),
            payment_destinations: new PaymentDestinationRepository(),
            cashiers:  new EmployeeRepository({
                limitClinics: this.$isAccessLimited('payments'),
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.CASHIER},
            }),
            payment_types: this.setPaymentTypes(),
            payment_kinds: this.setPaymentKinds(),
            paymentMethods: new PaymentMethodRepository({
                filters: {is_enabled: true},
            }),
        }
    },
    watch: {
        ['filter.clinic'](value) {
            this.specializations.setFilters({
                clinic: value,
            }, true);

            this.doctors.setFilters({
                clinic: value,
            }, true);

            this.cashiers.setFilters({
                clinic: value,
            }, true);
        },
        ['filter.card_specialization'](value) {
            this.doctors.setFilters({
                specialization: value,
            }, true);
        },
        ['filter.doctor_specialization'](value) {
            this.doctors.setFilters({
                specialization: value,
            }, true);
        },
    },
    methods: {
        changed(filters) {
            if (this.$isAccessLimited('payments') && filters.clinic.length === 0) {
                filters.clinic = this.primaryClinicId;
            }
            this.$emit('changed', this.makeFilters(filters));
        },
        initFilter(fromState = {}){
            let today = this.$moment().format('YYYY-MM-DD');

            this.filter = {
                clinic: this.primaryClinicId,
                patient: null,
                patient_name: null,
                doctor: [],
                cashier: null,
                patient_card_number: null,
                card_specialization: [],
                doctor_specialization: [],
                createdStart: today,
                createdEnd: today,
                cashbox_payment_method: [],
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
    }
}
</script>
