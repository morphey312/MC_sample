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
                <form-input
                    :entity="filter"
                    property="patient_name"
                    :clearable="true"
                    :label="__('Пациент')" />
                <form-input-search
                    :entity="filter"
                    property="patient_card_number"
                    :clearable="true"
                    :label="__('№ карты')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="clinic"
                    :label="__('Клиника')" />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="specialization"
                    :label="__('Специализация записи')" />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Период записи пациентов')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="date_start"
                            :editable="false"
                            :clearable="true" />
                        <form-date
                            :entity="filter"
                            property="date_end"
                            :editable="false"
                            :clearable="true" />
                    </div>
                </form-row>
                <form-select
                    :entity="filter"
                    :repository="doctors"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="doctor"
                    :label="__('Врач')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="reason_refusing_treatment"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="rejection_reason"
                    :label="__('Причины')" />
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
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointment-delays')
            }),
            specializations: new SpecializationRepository({
                limitClinics: this.$isAccessLimited('treatment-refusings')
            }),
            doctors: new EmployeeRepository(),
        };
    },
    mounted() {
        this.$watch('filter.clinic', () => {
            this.specializations.setFilters(this.getSpecializationFilters());
            this.doctors.setFilters(this.getDoctorFilters());
        });
    },
    methods: {
        initFilter(fromState = {}) {
            let today = this.$moment().format('YYYY-MM-DD');
            this.filter = {
                clinic: [],
                rejection_reason: [],
                specialization: [],
                date_start: today,
                date_end: today,
                doctor: [],
                ...fromState,
            };
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filter.clinic,
            });
        },
        getDoctorFilters() {
            return _.onlyFilled({
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                status: CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                clinic: this.filter.clinic,
                specialization: this.filter.specialization,
                date_employment_start: this.filter.date_start,
                date_employment_end: this.filter.date_end
            });
        },
    },
};
</script>
