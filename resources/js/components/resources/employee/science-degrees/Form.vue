<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :options="countries"
                    property="country_id"
                    :label="__('Страна')" />
                <form-input
                    :entity="model"
                    property="city"
                    :label="__('Город')" />
                <form-input
                    :entity="model"
                    property="institution_name"
                    :label="__('Учреждение')" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="speciality"
                    :label="__('Специальность')" />
                <form-select
                    :entity="model"
                    options="science_degree"
                    property="degree"
                    :label="__('Степень')" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="diploma_number"
                    :label="__('Номер диплома')" />
                <form-date
                    :entity="model"
                    property="issued_date"
                    :label="__('Дата выдачи')" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import CountryRepository from '@/repositories/country';

export default {
    props: {
        model: {
            type: Object
        },
    },
    data() {
        return {
            countries: new CountryRepository(),
        };
    },
}
</script>
