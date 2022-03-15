<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-input
                    :entity="model"
                    property="name"
                    :label="__('Название')" />
            </el-col>
            <el-col :span="8">
                <form-row  
                    name="for_has_day_sheet"
                    label="&nbsp;">
                    <form-checkbox
                        :entity="model"
                        property="has_day_sheet"
                        :label="__('Кабинет с табелем рабочего времени')" />
                </form-row>
            </el-col>
            <el-col :span="4">
                <form-row  
                    name="for_active"
                    label="&nbsp;">
                    <form-checkbox
                        :entity="model"
                        property="is_active"
                        :label="__('Активный')" />
                </form-row>
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="12">
                <form-checkbox
                    :entity="model"
                    property="is_operational"
                    :label="__('Является операционной')" />
            </el-col>
            <el-col :span="8">
                <form-checkbox
                    :entity="model"
                    property="is_hospital_room"
                    :label="__('Является палатой')" />
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