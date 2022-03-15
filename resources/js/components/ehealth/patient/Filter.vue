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
                    property="last_name"
                    :required="true"
                    :clearable="true"
                    :label="__('Фамилия')" />
                <form-date
                    :entity="filter"
                    property="birth_date"
                    :required="true"
                    name="birth_dates"
                    :label="__('Дата рождения')" />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    property="first_name"
                    :required="true"
                    :clearable="true"
                    :label="__('Имя')" />
                <form-input-search
                    :entity="filter"
                    property="phone_number"
                    :clearable="true"
                    :label="__('Телефон')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    property="second_name"
                    :clearable="true"
                    :label="__('Отчество')" />
                <form-input
                    :entity="filter"
                    property="tax_id"
                    :clearable="true"
                    :label="__('ИНН')" />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    property="birth_certificate"
                    :clearable="true"
                    :label="__('Номер свидетельства про рождение')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import FilterMixin from '@/mixins/filter';
import InformationSourceRepository from '@/repositories/patient/information-source';

export default {
    mixins: [
        FilterMixin,
    ],
    methods: {
        initFilter(fromState = {}) {
            this.filter =  {
                first_name: null,
                last_name: null,
                second_name: null,
                phone_number: null,
                birth_date: null,
                birth_certificate: null,
                tax_id: null,
                ...fromState,
            };
        },
    },
};
</script>
