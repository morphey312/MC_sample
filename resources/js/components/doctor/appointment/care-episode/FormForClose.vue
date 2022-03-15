<template>
    <model-form :model="model">
        <form-text
            :entity="model"
            property="close_summary"
            :label="__('Заключительное резюме')" />
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    property="status_reason"
                    :label="__('Причина закрытия эпизода')"
                    options="ehealth_episode_closing_reasons" />
            </el-col>
            <el-col :span="12">
                <form-date
                    :entity="model"
                    property="date_end"
                    :label="__('Дата закрытия')" />
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
