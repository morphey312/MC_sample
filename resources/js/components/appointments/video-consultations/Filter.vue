<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
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
                    :repository="patients"
                    :clearable="true"
                    property="patient"
                    :label="__('Пациент')"
                />
            </el-col>
            <el-col :span="6">
                <form-date
                    :entity="filter"
                    property="appointment_date"
                    :label="__('Дата записи')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="yes_no"
                    :clearable="true"
                    property="video"
                    :label="__('Ссылка на видео')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import BaseFilterMixin from '@/mixins/treatment/filter/base-filter';
import ServiceFilterMixin from '@/mixins/treatment/filter/service-filter';
import EmployeeRepository from "@/repositories/employee";
import CONSTANTS from "@/constants";
import PatientRepository from "@/repositories/patient";

export default {
    mixins: [
        FilterMixin,
        BaseFilterMixin,
        ServiceFilterMixin,
    ],
    data(){
        return {
            doctors: new EmployeeRepository({
                filters: {
                    positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                },
            }),
            patients: new PatientRepository(),
        }
    }
};

</script>
