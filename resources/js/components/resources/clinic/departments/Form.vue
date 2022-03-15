<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="name"
                    :label="__('Название отделения')" />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="model"
                    property="clinic_id"
                    :options="clinics"
                    :filterable="true"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="model"
                    property="type"
                    options="department_type"
                    :label="__('Тип отделения')" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>
<script>
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        model: {
            type: Object
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('departments'),
            }),
        }
    }
};
</script>
