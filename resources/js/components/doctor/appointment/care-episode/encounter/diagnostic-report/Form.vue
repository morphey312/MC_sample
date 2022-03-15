<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    property="category"
                    :label="__('Категория диагностического отчета')"
                    options="ehealth_diagnostic_report_categories" />
            </el-col>
            <el-col :span="12">
                <form-select
                    :entity="model"
                    :options="services"
                    :clearable="true"
                    property="code"
                    :label="__('Услуга')" />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    :options="clinics"
                    :clearable="true"
                    property="division"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="12">
                <form-date
                    :entity="model"
                    property="issued"
                    :type="'datetime'"
                    format="yyyy-MM-dd HH:mm:ss"
                    value-format="yyyy-MM-dd HH:mm:ss"
                    :label="__('Дата предоставления услуг/получения отчета')" />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-date
                    :entity="model"
                    property="effective_period_start"
                    :type="'datetime'"
                    format="yyyy-MM-dd HH:mm:ss"
                    value-format="yyyy-MM-dd HH:mm:ss"
                    :label="__('Начало получнеия показателей')" />
            </el-col>
            <el-col :span="12">
                <form-date
                    :entity="model"
                    property="effective_period_end"
                    :type="'datetime'"
                    format="yyyy-MM-dd HH:mm:ss"
                    value-format="yyyy-MM-dd HH:mm:ss"
                    :label="__('Конец получения показателей')" />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-checkbox
                    :entity="model"
                    property="primary_source"
                    :label="__('Первичный источник')"/>
            </el-col>
            <el-col :span="12">
                <form-checkbox
                    :entity="model"
                    property="paper_referral"
                    :label="__('Бумажное направление')"/>
            </el-col>
        </el-row>
        <div v-if="model.paper_referral" style="margin-top: 20px; margin-bottom: 20px">
            <paper-referral-block :model="model"/>
        </div>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :clearable="true"
                    :filterable="true"
                    :entity="model"
                    property="performer"
                    :label="__('Исполнитель диагностики')"
                    :repository="doctorsRepository"/>
            </el-col>
            <el-col :span="12">
                <form-select
                    :clearable="true"
                    :filterable="true"
                    :entity="model"
                    property="results_interpreter"
                    :label="__('Работник что интерпретировал результаты')"
                    :repository="doctorsRepository"/>
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-text
                    :entity="model"
                    property="conclusion"
                    :label="__('Заключение доктора')"
                />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    :multiple="true"
                    :repository="diagnosisRepository"
                    :collapse-tags="false"
                    property="conclusion_code"
                    :label="__('Диагноз по МКБ')"
                />
            </el-col>
            <el-col :span="12">
                <form-select
                    :clearable="true"
                    :filterable="true"
                    :entity="model"
                    property="recorded_by"
                    :label="__('Доктор, который передает в систему отчет')"
                    :repository="doctorsRepository"/>
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import PaperReferralBlock from '../blocks/PaperReferral.vue';

export default {
    components: {
        PaperReferralBlock
    },
    props: {
        model: Object,
        services: Array,
        clinics: Object,
        doctorsRepository: Object,
        diagnosisRepository: Object,
    },
}
</script>
