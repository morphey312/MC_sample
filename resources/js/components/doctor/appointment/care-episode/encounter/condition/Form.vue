<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    property="clinical_status"
                    :label="__('Клинический статус')"
                    options="ehealth_condition_clinical_statuses" />
            </el-col>
            <el-col :span="12">
                <form-select
                    :entity="model"
                    property="verification_status"
                    :label="__('Вид диагноза')"
                    options="ehealth_condition_verification_statuses" />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    property="severity"
                    :label="__('Тяжость состояния')"
                    options="ehealth_condition_severities" />
            </el-col>
            <el-col :span="12">
                <form-select
                    :entity="model"
                    :multiple="true"
                    property="body_sites"
                    :label="__('Части тела')"
                    options="ehealth_body_sites" />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-date
                    :entity="model"
                    property="onset_date"
                    :type="'datetime'"
                    format="yyyy-MM-dd HH:mm:ss"
                    value-format="yyyy-MM-dd HH:mm:ss"
                    :label="__('Дата установления диагноза')"/>
            </el-col>
            <el-col :span="12">
                <form-checkbox
                    :entity="model"
                    property="primary_source"
                    :label="__('Первичный источник')"/>
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-select
                    :entity="outpatientRecord"
                    :disabled="true"
                    :multiple="true"
                    :collapse-tags="false"
                    property="diagnosis_icd_names"
                    :label="__('Диагноз по МКБ')"
                />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: Object,
        outpatientRecord: Object,
    },
}
</script>
