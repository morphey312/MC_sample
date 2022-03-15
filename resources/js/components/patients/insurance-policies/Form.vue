<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    :options="insuranceCompanies"
                    property="insurance_company_id"
                    :label="__('Страховая компания')" />
                <form-input
                    :entity="model"
                    property="number"
                    :label="__('Номер полиса')" />
                <form-date
                    :entity="model"
                    property="expires"
                    :label="__('Дата окончания действия полиса')" />
            </el-col>
            <el-col :span="12">
                <form-text
                    :entity="model"
                    property="comment"
                    :rows="8"
                    :label="__('Примечание')" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>
<script>
import InsuranceCompanyRepository from '@/repositories/insurance-company';

export default {
    props: {
        model: {
            type: Object
        },
        insuranceCompanies: {
            type: Array,
            default: [],
        },
    },
}
</script>