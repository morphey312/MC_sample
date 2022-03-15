<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :options="clinics"
                    property="clinic_id"
                    :filterable="true"
                    :label="__('Клиника')"
                />
                <form-select
                    :entity="model"
                    :options="specializations"
                    property="specialization_id"
                    :label="__('Специализация')"
                />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :options="doctors"
                    property="employee_id"
                    :label="__('Врач')"
                />
                <form-select
                    :entity="model"
                    options="doctor_plan_service_mark"
                    property="plan_service_mark"
                    :label="__('Опция специалиста')"
                />
            </el-col>
            <el-col :span="8">
                <form-date
                    :entity="model"
                    property="year"
                    type="year"
                    format="yyyy"
                    value-format="yyyy"
                    :label="__('Отчетный год')"
                />
            </el-col>
        </el-row>
        <el-row>
            <month-table :model="model" />
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import ProxyRepository from '@/repositories/proxy-repository';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import MonthTable from './MonthTable.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        MonthTable,
    },
    props: {
        model: Object
    },
    data() {
        let specializations = new SpecializationRepository({
            limitClinics: this.$isAccessLimited('appointments'),
            filters: this.getSpecializationFilters(),
        });

        return {
            specializations,
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            }),
            doctors: new EmployeeRepository({
                filters: this.getDoctorFilters(),
            }),
        };
    },
    methods: {
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.model.clinic_id,
            });
        },
        getDoctorFilters() {
            return _.onlyFilled({
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                clinic: this.model.clinic_id,
                specialization: this.model.specialization_id,
            });
        },
    },
    watch: {
        ['model.clinic_id']() {
            this.specializations.setFilters(this.getSpecializationFilters());
            this.doctors.setFilters(this.getDoctorFilters());
        },
        ['model.specialization_id']() {
            this.doctors.setFilters(this.getDoctorFilters());
        },
    },
}
</script>
