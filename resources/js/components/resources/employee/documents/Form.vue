<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    options="person_document"
                    property="type"
                    :label="__('Тип')" />
                <form-input
                    :entity="model"
                    property="number"
                    :label="__('Номер')" />
                <form-date
                    :entity="model"
                    property="issued_at"
                    :label="__('Дата выдачи')" />
            </el-col>
            <el-col :span="12">
                <form-text
                    :entity="model"
                    property="issued_by"
                    :rows="3"
                    :label="__('Кем выдан')" />
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
