<template>
    <el-row :gutter="20">
        <el-col :span="12">
            <form-text
                :entity="model"
                :readonly="true"
                property="outcome"
                :label="__('Комментарий оператора')"
            />
        </el-col>
        <el-col :span="12">
            <form-row
                name="feedback_attachments"
                :label="__('Прикрепленные файлы')">
                <file-attachments :attachments="model.feedback_attachments_data" />
            </form-row>
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
