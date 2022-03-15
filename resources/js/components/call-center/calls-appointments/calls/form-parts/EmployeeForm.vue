<template>
    <el-row :gutter="20">
        <el-col :span="6">
            <form-input
                :entity="model"
                property="contact.last_name"
                :label="__('Фамилия')"
            />
        </el-col>
        <el-col :span="6">
            <form-input
                :entity="model"
                property="contact.first_name"
                :label="__('Имя')"
            />
        </el-col>
        <el-col :span="6">
            <form-input
                :entity="model"
                property="contact.middle_name"
                :label="__('Отчество')"
            />
        </el-col>
        <el-col :span="6">
            <form-input
                :entity="model"
                property="contact.phone"
                :label="__('Телефон')"
            />
        </el-col>
    </el-row>
</template>

<script>
export default {
    props: {
        model: {
            type: Object,
            required: true
        },
    },
}
</script>
