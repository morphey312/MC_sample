<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="10">
                <form-select
                    :entity="model"
                    :options="clinics"
                    property="clinic_id"
                    :filterable="true"
                    :label="__('Клиника')" />
                <form-checkbox
                    :entity="model"
                    property="agreement_active"
                    :label="__('Активный статус договора')" />
            </el-col>
            <el-col :span="14">
                <form-input
                    :entity="model"
                    property="agreement"
                    :label="__('Данные по подписанному договору')" />
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
            type: Object,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository(),
        };
    },
}
</script>
