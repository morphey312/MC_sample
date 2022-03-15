<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :options="types"
                    property="speciality_id"
                    :label="__('Специализация')" />
                <form-select
                    :entity="model"
                    options="speciality_level"
                    property="level"
                    :label="__('Уровень')" />
                <form-select
                    :entity="model"
                    options="speciality_qualy_type"
                    property="qualification_type"
                    :label="__('Тип квалификации')" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="attestation_name"
                    :label="__('Учреждение, проводившее аттестацию')" />
                <form-date
                    :entity="model"
                    property="attestation_date"
                    :label="__('Дата аттестации')" />
                <form-checkbox
                    :entity="model"
                    property="primary"
                    :label="__('Основная рабочая специальность')" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="certificate_number"
                    :label="__('Номер сертификата')" />
                <form-date
                    :entity="model"
                    property="valid_to_date"
                    :label="__('Дата истечения срока сертификата')" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import EmployeeSpecialityTypeRepository from '@/repositories/employee/speciality-type';

export default {
    props: {
        model: {
            type: Object
        },
    },
    data() {
        return {
            types: new EmployeeSpecialityTypeRepository(),
        };
    },
}
</script>
