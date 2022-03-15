<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="model"
                    options="qualification_type"
                    property="type"
                    :label="__('Тип')" />
                <form-input
                    :entity="model"
                    property="institution_name"
                    :label="__('Название учреждения')" />
                <form-input
                    :entity="model"
                    property="speciality"
                    :label="__('Специальность')" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="certificate_number"
                    :label="__('Номер сертификата')" />
                <form-date
                    :entity="model"
                    property="issued_date"
                    :label="__('Дата выдачи')" />
                <form-date
                    :entity="model"
                    property="valid_to"
                    :label="__('Действителен до')" />
            </el-col>
            <el-col :span="8">
                <form-text
                    :entity="model"
                    property="additional_info"
                    :rows="3"
                    :label="__('Дополнительная информация')" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: {
            type: Object
        },
    },
}
</script>
