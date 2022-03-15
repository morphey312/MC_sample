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
                    :options="patient_cards"
                    :clearable="true"
                    property="card_specialization"
                    :label="__('Специализация карты')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    property="clinic"
                    :multiple="true"
                    :filterable="true"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :multiple="true"
                    property="doctor_specialization"
                    :label="__('Специализация врача')" />

            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="cashiers"
                    :clearable="true"
                    property="cashier"
                    :label="__('Кассир')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import FilterMixin from '@/mixins/filter';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        patient: Object,
        filterClinics: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('payments'),
            }),
            specializations: new SpecializationRepository({
                limitClinics: this.$isAccessLimited('payments'),
            }),
            cashiers: new EmployeeRepository({
                filters: this.getCashierFilters(),
            }),
            patient_cards: this.getPatientCards(),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter =  {
                card_specialization: null,
                clinic: [],
                doctor_specialization: (this.$store.state.user.isDoctor ? this.$store.state.user.specializations: []),
                cashier: null,
                ...fromState,
            };
        },
    },
    methods: {
        getPatientCards() {
            let cards = [];
            this.patient.cards.forEach((card) => {
                card.specializations.forEach((specialization) => {
                    cards.push({
                        id: specialization.specialization_id,
                        value: `${card.number}-${specialization.specialization.short_name}`,
                    })
                });
            });
            return cards;
        },
        getCashierFilters() {
            return {
                employee_clinic: {
                    clinic: this.filterClinics,
                    position_type: CONSTANTS.EMPLOYEE.POSITIONS.CASHIER,
                },
            }
        },
    },
};
</script>
