<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="model"
                    :options="clinics"
                    property="clinic_id"
                    :filterable="true"
                    :label="__('Клиника')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="appointment_norm"
                    :label="__('Норма по записи, %')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="income_norm"
                    :label="__('Норма по приходу, %')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="mistakes_norm"
                    :label="__('Кол-во ошибок')"
                />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="night_repeated_patient"
                    :label="__('Повторные записи(ночные)')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="day_repeated_patient"
                    :label="__('Повторные записи(дневные)')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="night_post_call"
                    :label="__('Поствызов ночной, сек')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="day_post_call"
                    :label="__('Поствызов дневной, сек')"
                />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="rate_minimum"
                    :label="__('Ставка минимальная')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="rate_medium"
                    :label="__('Ставка средняя')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="rate_maximum"
                    :label="__('Ставка максимальная')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="evaluation_norm"
                    :label="__('Норма по оцен. листу, %')"
                />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        model: Object
    },
    data() {
        return {
            clinics: new ClinicRepository(),
        };
    },
};
</script>
