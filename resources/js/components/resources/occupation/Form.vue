<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-date
                    :entity="model"
                    property="date_from"
                    :label="__('Дата начала')" />
                <form-time
                    :entity="model"
                    property="start"
                    start="00:00"
                    end="24:00"
                    step="00:30"
                    :label="__('Время начала')" />
            </el-col>
            <el-col :span="8">
                <form-date
                    :entity="model"
                    property="date_to"
                    :label="__('Дата окончания')" />
                <form-time
                    :entity="model"
                    property="end"
                    start="00:00"
                    end="24:00"
                    step="00:30"
                    :label="__('Время окончания')" />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="model"
                    property="status"
                    options="place_occupation_status"
                    :label="__('Статус')" />
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