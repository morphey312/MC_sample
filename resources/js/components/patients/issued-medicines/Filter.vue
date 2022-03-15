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
                <div class="form-input-group">
                    <form-input
                        :entity="filter"
                        property="patient_card_number"
                        :clearable="true"
                        :label="__('№ карты')" />
                </div>
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :multiple="true"
                    property="specialization_card_id"
                    :label="__('Специализация карты')" />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    property="medicine_name"
                    :clearable="true"
                    :label="__('Название')" />
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="clinic"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="create_dates"
                    :label="__('Период выдачи медикамента')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="created_start"/>
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="created_end"/>
                    </div>
                </form-row>
                <form-select
                    :entity="filter"
                    :repository="employees"
                    :clearable="true"
                    :filterable="true"
                    property="operator_id"
                    :label="__('Оператор')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :filterable="true"
                    :clearable="true"
                    :min-query-len="0"
                    :limit="80"
                    :repository="doctors"
                    property="doctor_id"
                    :multiple="true"
                    :label="__('Врач')" />
                <form-checkbox
                    :entity="filter"
                    property="include_not_working"
                    :label="__('Показать неработающих врачей')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import SpecializationRepository from '@/repositories/specialization';
import FilterMixin from '@/mixins/filter';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            }),
            laboratories: new LaboratoryRepository(),
            specializations: new SpecializationRepository(),
            employees: new EmployeeRepository(),
            doctors: new EmployeeRepository({
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                status: CONSTANTS.EMPLOYEE.STATUSES.WORKING
            },[{direction: 'asc', field: 'full_name'}]),
        };
    },
    watch: {
        ['filter.clinic'](){
            this.specializations.setFilters(
                _.onlyFilled({
                    clinic: this.filter.clinic,
                }));
            this.doctors.setFilters(
                _.onlyFilled({
                    positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                    clinic: this.filter.clinic,
                    status: !this.filter.include_not_working ? CONSTANTS.EMPLOYEE.STATUSES.WORKING : null
                }))
        },
        ['filter.include_not_working'](value) {
            this.doctors.setFilters(
                _.onlyFilled({
                    positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                    clinic: this.filter.clinic,
                    status: !value ? CONSTANTS.EMPLOYEE.STATUSES.WORKING : null
                })
            );
        },
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: [],
                include_not_working: false,
                specialization_card_id: [],
                ...fromState,
            };
        },
        changed(filters) {
            if (this.$isAccessLimited('appointments') && filters.clinic.length === 0) {
                filters.clinic = this.$store.state.user ? this.$store.state.user.clinics : [];
            }
            this.$emit('changed', this.makeFilters(filters));
        },
    },
};
</script>
