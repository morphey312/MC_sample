<template>
    <model-form :model="model">
        <form-input 
            :entity="model"
            property="name"
            :label="__('Название эпизода')" />
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    property="type"
                    :label="__('Тип эпизода')"
                    options="ehealth_episode_type" />
            </el-col>
            <el-col :span="12">
                <form-date
                    :entity="model"
                    property="date_start"
                    :label="__('Дата начала')" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
export default {
    props: {
        model: Object,
    },
}
</script>