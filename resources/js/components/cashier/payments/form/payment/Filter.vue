<template>
	<search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        :button-submit-text="__('Фильтровать')"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="5">
                <form-input
                    :entity="filter"
                    property="patient_card_number"
                    :clearable="true"
                    :label="__('Номер карты')" />
            </el-col>
            <el-col :span="5">
                <form-select
                    :entity="filter"
                    :options="clinics_list"
                    property="appointment_clinic"
                    :clearable="true"
                    :filterable="true"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="5">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    property="doctor_specialization"
                    :label="__('Специализация врача')" />
            </el-col>
            <el-col :span="5">
                <form-select
                    :entity="filter"
                    :options="cashiers"
                    :clearable="true"
                    :filterable="true"
                    property="cashier"
                    :label="__('Кассир')" />
            </el-col>
        </el-row>
	</search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
	mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics_list: [],
            specializations: [],
            cashiers: [],
        }
    },
    beforeMount() {
        this.initFilter();
    },
    mounted() {
        this.getLists();
    },
    
    methods: {
    	initFilter(fromState = {}){
            this.filter = {
                patient_card_number: null,
                appointment_clinic: null,
                doctor_specialization: null,
                ...fromState,
            };
        },
        getLists() {
            this.getClinics();
            this.getSpecializations();
            this.getCashiers();
        },
        getClinics() {
            let clinic = new ClinicRepository();
            clinic.fetchList().then((response) => {
                this.clinics_list = response;
            });
        },
        getSpecializations() {
            let specialization = new SpecializationRepository();
            specialization.fetchList().then((response) => {
                this.specializations = response;
            });
        },
        getCashiers() {
            let employee = new EmployeeRepository();
            employee.fetchList({positionType: CONSTANTS.EMPLOYEE.POSITIONS.CASHIER}).then((response) => {
                this.cashiers = response;
            });
        },
    },
}	

</script>