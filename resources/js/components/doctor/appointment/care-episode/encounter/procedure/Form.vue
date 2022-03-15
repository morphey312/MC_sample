<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    :options="services"
                    :clearable="true"
                    property="code"
                    :label="__('Услуга')" />
            </el-col>
            <el-col :span="12">
                <form-select
                    :entity="model"
                    options="ehealth_procedure_categories"
                    property="category"
                    :label="__('Категоря процедуры')" />
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
                <form-select
                    :clearable="true"
                    :filterable="true"
                    :entity="model"
                    property="recorded_by"
                    :label="__('Кем внесено в систему')"
                    :repository="doctorsRepository"/>
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :clearable="true"
                    :filterable="true"
                    :entity="model"
                    property="performer"
                    :label="__('Исполнитель процедуры')"
                    :repository="doctorsRepository"/>
            </el-col>
            <el-col :span="12">
                <form-select
                    :entity="model"
                    property="outcome"
                    :label="__('Результат проведення процедуры')"
                    options="ehealth_procedure_outcomes"/>
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
            <el-col :span="24">
                <form-text
                    :entity="model"
                    property="note"
                    :label="__('Заметки')"
                />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-date
                    :entity="model"
                    property="performed_date_time"
                    :type="'datetime'"
                    format="yyyy-MM-dd HH:mm:ss"
                    value-format="yyyy-MM-dd HH:mm:ss"
                    :label="__('Дата и время')" />
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
    },
}
</script>
