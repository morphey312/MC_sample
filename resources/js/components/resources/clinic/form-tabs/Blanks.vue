<template>
    <el-row :gutter="20">
        <el-col :span="8">
            <form-upload
                ref="attachments"
                :entity="model.header_blank"
                :limit="1"
                property="attachments"
                :label="__('Шапка бланка')" />
        </el-col>
        <el-col :span="8">
            <form-upload
                ref="attachments"
                :entity="model.footer_blank"
                :limit="1"
                property="attachments"
                :label="__('Подвал бланка')" />
        </el-col>
        <el-col :span="8">
            <form-upload
                :entity="model"
                :limit="1"
                :multiple="false"
                property="questionnaire_blank"
                :label="__('Бланк анкеты пациента')" />
        </el-col>
    </el-row>
</template>
<script>
export default {
    props: {
        model: Object,
    },

}
</script>
